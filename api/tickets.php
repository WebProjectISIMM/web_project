<?php
session_start();
header('Content-Type: application/json');
include "../test.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    // CREATE NEW TICKET
    if ($method === 'POST' && $action === 'create') {
        $data = json_decode(file_get_contents('php://input'), true);

        $agency            = trim($data['agency']            ?? '');
        $location          = trim($data['location']          ?? '');
        $service_key       = trim($data['service_key']       ?? '');
        $establishment_key = trim($data['establishment_key'] ?? '');

        if (!$agency || !$location || !$establishment_key) {
            throw new Exception('Données de ticket manquantes');
        }

        // ── 1. Sequential ticket number per establishment ──────────────────
        // Count ALL tickets ever issued (any status) so numbers never repeat.
        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS total FROM tickets WHERE establishment_key = ?"
        );
        $stmt->bind_param("s", $establishment_key);
        $stmt->execute();
        $next_num = (int)$stmt->get_result()->fetch_assoc()['total'] + 1;
        $stmt->close();

        $ticket_number = 'A-' . str_pad($next_num, 3, '0', STR_PAD_LEFT);

        // ── 2. Real queue depth (people currently waiting) ─────────────────
        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS waiting FROM tickets
             WHERE establishment_key = ? AND status = 'waiting'"
        );
        $stmt->bind_param("s", $establishment_key);
        $stmt->execute();
        $people_ahead = (int)$stmt->get_result()->fetch_assoc()['waiting'];
        $stmt->close();

        // ── 3. Estimated wait time (5 min per person ahead, min 1 min) ─────
        $wait_minutes = max(1, $people_ahead * 5);
        $wait_time    = '~' . $wait_minutes . ' min';

        // ── 4. Insert ticket ───────────────────────────────────────────────
        $stmt = $conn->prepare(
            "INSERT INTO tickets
                (user_id, ticket_number, agency, location, service_key, establishment_key, wait_time, people_ahead)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "issssssi",
            $user_id, $ticket_number, $agency, $location,
            $service_key, $establishment_key, $wait_time, $people_ahead
        );

        if ($stmt->execute()) {
            echo json_encode([
                'success'       => true,
                'message'       => 'Ticket créé avec succès',
                'ticket_id'     => $conn->insert_id,
                'ticket_number' => $ticket_number,
                'wait_time'     => $wait_time,
                'people_ahead'  => $people_ahead
            ]);
        } else {
            throw new Exception('Erreur lors de la création du ticket');
        }
        $stmt->close();
    }

    // GET USER'S TICKETS
    elseif ($method === 'GET' && $action === 'get-user-tickets') {
        $stmt = $conn->prepare(
            "SELECT id, ticket_number, agency, location, wait_time, people_ahead, status, created_at 
             FROM tickets 
             WHERE user_id = ? AND status = 'waiting'
             ORDER BY created_at DESC"
        );
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tickets = $result->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode([
            'success' => true,
            'tickets' => $tickets
        ]);
        $stmt->close();
    }

    // CANCEL TICKET
    elseif ($method === 'POST' && $action === 'cancel') {
        $data = json_decode(file_get_contents('php://input'), true);
        $ticket_id = (int)($data['ticket_id'] ?? 0);

        if (!$ticket_id) {
            throw new Exception('ID de ticket invalide');
        }

        // Verify ticket belongs to user (or allow if user is an agent)
        if ($_SESSION['user_role'] === 'client') {
            $stmt = $conn->prepare("SELECT id FROM tickets WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $ticket_id, $user_id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                throw new Exception('Ticket non trouvé ou non autorisé');
            }
            $stmt->close();
        }

        $stmt = $conn->prepare("UPDATE tickets SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $ticket_id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Ticket annulé avec succès'
            ]);
        } else {
            throw new Exception('Erreur lors de l\'annulation du ticket');
        }
        $stmt->close();
    }

    // GET QUEUE FOR AGENTS
    elseif ($method === 'GET' && $action === 'get-queue') {
        $establishment = $_GET['establishment'] ?? '';
        
        if (!$establishment) {
            throw new Exception('Établissement non spécifié');
        }

        $stmt = $conn->prepare(
            "SELECT t.id, t.ticket_number, t.agency, t.location, t.created_at, t.people_ahead, t.wait_time, u.name 
             FROM tickets t
             LEFT JOIN users u ON t.user_id = u.id
             WHERE t.establishment_key = ? AND t.status = 'waiting'
             ORDER BY t.created_at ASC"
        );
        $stmt->bind_param("s", $establishment);
        $stmt->execute();
        $result = $stmt->get_result();
        $queue = $result->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode([
            'success' => true,
            'queue' => $queue,
            'queue_count' => count($queue)
        ]);
        $stmt->close();
    }

    // MARK TICKET AS SERVED
    elseif ($method === 'POST' && $action === 'serve') {
        // Only agents can mark tickets as served
        if ($_SESSION['user_role'] === 'client') {
            throw new Exception('Non autorisé');
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $ticket_id = (int)($data['ticket_id'] ?? 0);

        if (!$ticket_id) {
            throw new Exception('ID de ticket invalide');
        }

        $stmt = $conn->prepare("UPDATE tickets SET status = 'served', served_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $ticket_id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Ticket marqué comme servi'
            ]);
        } else {
            throw new Exception('Erreur lors de la mise à jour du ticket');
        }
        $stmt->close();
    }

    else {
        throw new Exception('Action non reconnue');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
