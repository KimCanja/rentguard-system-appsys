<?php
// includes/sos-button.php
// Check if session is already started before starting it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Make sure database.php is included for functions
if (!function_exists('redirect')) {
    require_once __DIR__ . '/../config/database.php';
}

// Check if user has active rental
$has_active_rental = false;
$active_rental = null;

if (isset($_SESSION['user_id']) && isset($pdo)) {
    $stmt = $pdo->prepare("
        SELECT r.*, v.model, v.plate_number 
        FROM rentals r 
        JOIN vehicles v ON r.vehicle_id = v.vehicle_id 
        WHERE r.user_id = ? AND r.status = 'active'
        ORDER BY r.created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $active_rental = $stmt->fetch();
    $has_active_rental = !empty($active_rental);
}
?>

<style>
.sos-button-container {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 9999;
}

.sos-button {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
    color: white;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    animation: pulse 2s infinite;
}

.sos-button:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.6);
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
    }
    70% {
        box-shadow: 0 0 0 15px rgba(239, 68, 68, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
    }
}

.sos-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 10000;
    justify-content: center;
    align-items: center;
}

.sos-modal-content {
    background: white;
    border-radius: 20px;
    max-width: 500px;
    width: 90%;
    padding: 30px;
    position: relative;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.sos-modal-header {
    text-align: center;
    margin-bottom: 20px;
}

.sos-modal-header i {
    font-size: 60px;
    color: #EF4444;
    margin-bottom: 10px;
}

.sos-options {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin: 20px 0;
}

.sos-option {
    padding: 15px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.sos-option:hover {
    border-color: #EF4444;
    background: #FEE2E2;
}

.sos-option i {
    font-size: 24px;
    display: block;
    margin-bottom: 8px;
}

.sos-option.emergency { color: #EF4444; }
.sos-option.accident { color: #F59E0B; }
.sos-option.mechanical { color: #3B82F6; }
.sos-option.assault { color: #8B5CF6; }

.sos-message {
    width: 100%;
    padding: 10px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    margin: 15px 0;
    resize: vertical;
    font-family: inherit;
}

.sos-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.btn-sos-send {
    background: #EF4444;
    color: white;
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}

.btn-sos-send:hover {
    background: #DC2626;
}

.btn-sos-cancel {
    background: #64748B;
    color: white;
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}

.btn-sos-cancel:hover {
    background: #475569;
}

.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.sos-confirmation {
    text-align: center;
}

.sos-confirmation i {
    font-size: 60px;
    color: #10B981;
    margin-bottom: 15px;
}
</style>

<div class="sos-button-container">
    <button class="sos-button" onclick="openSOSModal()">
        <i class="fas fa-exclamation-triangle"></i>
    </button>
</div>

<div id="sosModal" class="sos-modal">
    <div class="sos-modal-content" id="sosModalContent">
        <div class="sos-modal-header">
            <i class="fas fa-exclamation-triangle"></i>
            <h2>Emergency SOS</h2>
            <p class="text-muted">Select the type of emergency assistance you need</p>
        </div>
        
        <div class="sos-options">
            <div class="sos-option emergency" onclick="selectSOSType('emergency')">
                <i class="fas fa-ambulance"></i>
                <strong>Emergency</strong>
                <small>Medical/Fire/Police</small>
            </div>
            <div class="sos-option accident" onclick="selectSOSType('accident')">
                <i class="fas fa-car-crash"></i>
                <strong>Accident</strong>
                <small>Vehicle collision</small>
            </div>
            <div class="sos-option mechanical" onclick="selectSOSType('mechanical')">
                <i class="fas fa-wrench"></i>
                <strong>Mechanical</strong>
                <small>Breakdown issues</small>
            </div>
            <div class="sos-option assault" onclick="selectSOSType('assault')">
                <i class="fas fa-shield-alt"></i>
                <strong>Assault</strong>
                <small>Personal safety</small>
            </div>
        </div>
        
        <textarea class="sos-message" id="sosMessage" rows="3" placeholder="Describe your situation (optional)..."></textarea>
        
        <div class="sos-actions">
            <button class="btn-sos-cancel" onclick="closeSOSModal()">Cancel</button>
            <button class="btn-sos-send" id="sendSOSBtn" onclick="sendSOSAlert()">Send SOS Alert</button>
        </div>
    </div>
</div>

<script>
let selectedSOSType = 'emergency';

function openSOSModal() {
    document.getElementById('sosModal').style.display = 'flex';
}

function closeSOSModal() {
    document.getElementById('sosModal').style.display = 'none';
    document.getElementById('sosMessage').value = '';
    selectedSOSType = 'emergency';
}

function selectSOSType(type) {
    selectedSOSType = type;
    // Highlight selected option
    document.querySelectorAll('.sos-option').forEach(opt => {
        opt.style.borderColor = '#E2E8F0';
        opt.style.background = 'white';
    });
    event.currentTarget.style.borderColor = '#EF4444';
    event.currentTarget.style.background = '#FEE2E2';
}

function getLocation() {
    return new Promise((resolve, reject) => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(resolve, reject, {
                enableHighAccuracy: true,
                timeout: 10000
            });
        } else {
            reject(new Error('Geolocation not supported'));
        }
    });
}

async function sendSOSAlert() {
    const sendBtn = document.getElementById('sendSOSBtn');
    const originalText = sendBtn.innerHTML;
    sendBtn.innerHTML = '<span class="loading-spinner"></span> Sending...';
    sendBtn.disabled = true;
    
    try {
        // Get user location
        let location = null;
        
        try {
            const position = await getLocation();
            location = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
        } catch (error) {
            console.log('Location error:', error);
        }
        
        // Send SOS alert via AJAX
        const formData = new FormData();
        formData.append('action', 'send_sos');
        formData.append('alert_type', selectedSOSType);
        formData.append('message', document.getElementById('sosMessage').value);
        formData.append('rental_id', '<?php echo $active_rental['rental_id'] ?? ''; ?>');
        formData.append('vehicle_id', '<?php echo $active_rental['vehicle_id'] ?? ''; ?>');
        if (location) {
            formData.append('location_lat', location.lat);
            formData.append('location_lng', location.lng);
        }
        
        const response = await fetch('/rentguard/ajax/sos-handler.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Show success message
            document.getElementById('sosModalContent').innerHTML = `
                <div class="sos-confirmation">
                    <i class="fas fa-check-circle"></i>
                    <h3>Alert Sent Successfully!</h3>
                    <p>Our team has been notified and will contact you shortly.</p>
                    <p class="text-muted">For immediate emergency, please call:<br><strong>911</strong></p>
                    <button class="btn btn-primary mt-3" onclick="location.reload()">Close</button>
                </div>
            `;
        } else {
            throw new Error(result.message);
        }
        
    } catch (error) {
        alert('Failed to send SOS alert. Please call emergency services directly: 911');
        console.error(error);
        sendBtn.innerHTML = originalText;
        sendBtn.disabled = false;
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('sosModal');
    if (event.target === modal) {
        closeSOSModal();
    }
}
</script>