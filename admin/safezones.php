<?php
session_start();
require_once('../auth/db.php');
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Handle add safe zone form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_safezone'])) {
    $name = trim($_POST['name']);
    $location_description = trim($_POST['location_description']);
    $barangay = trim($_POST['barangay']);
    $latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : null;
    $longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : null;
    if ($name && $location_description && $barangay && $latitude && $longitude) {
        $stmt = $conn->prepare("INSERT INTO safezones (name, location_description, barangay, latitude, longitude, is_approved, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
        $stmt->bind_param("sssdd", $name, $location_description, $barangay, $latitude, $longitude);
        $stmt->execute();
        $stmt->close();
        header("Location: safezones.php?success=1");
        exit();
    } else {
        $error = "All fields including map location are required.";
    }
}

// Handle edit safe zone form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_safezone'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $location_description = trim($_POST['location_description']);
    $barangay = trim($_POST['barangay']);
    $latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : null;
    $longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : null;
    if ($id && $name && $location_description && $barangay && $latitude && $longitude) {
        $stmt = $conn->prepare("UPDATE safezones SET name=?, location_description=?, barangay=?, latitude=?, longitude=? WHERE id=?");
        $stmt->bind_param("sssddi", $name, $location_description, $barangay, $latitude, $longitude, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: safezones.php?edit_success=1");
        exit();
    } else {
        $error = "All fields are required for editing.";
    }
}

// Handle delete safe zone form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_safezone'])) {
    $id = intval($_POST['id']);
    if ($id) {
        $stmt = $conn->prepare("DELETE FROM safezones WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: safezones.php?delete_success=1");
        exit();
    } else {
        $error = "Invalid safe zone ID for deletion.";
    }
}

// Fetch all safe zones
$safezones = $conn->query("SELECT id, name, location_description, barangay, is_approved, created_at, latitude, longitude FROM safezones ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Safe Zones | HatidGawa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        :root {
            --primary-color: #F6E7D8;   /* Light beige background */
            --secondary-color: #BFA181; /* Medium brown accent */
            --text-color: #22211F;      /* Almost black for text */
            --card-bg: #FFF8F0;         /* Slightly lighter for cards */
            --border-color: #E5D3C0;    /* Subtle border */
            --badge-success: #BFA181;
            --badge-warning: #F6E7D8;
            --badge-danger: #E57373;
            --badge-secondary: #BFA181;
        }
        
        body {
            background-color: var(--primary-color);
            color: var(--text-color);
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            height: 100vh;
            background-color: var(--primary-color);
            color: var(--text-color);
            position: fixed;
            width: 250px;
            padding-top: 20px;
            border-right: 1px solid var(--border-color);
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .sidebar-nav {
            padding: 20px 0;
        }
        
        .sidebar-nav a {
            color: var(--text-color);
            padding: 10px 20px;
            display: block;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.2s;
        }
        
        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background-color: var(--secondary-color);
            color: var(--text-color);
        }
        
        .main-content {
            margin-left: 250px;
            padding: 24px;
            background-color: var(--primary-color);
            min-height: 100vh;
        }
        
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            color: var(--text-color);
            margin-bottom: 20px;
            background: var(--card-bg);
            box-shadow: 0 2px 8px rgba(191,161,129,0.05);
            border: 1px solid var(--border-color);
        }
        
        .stat-card i {
            font-size: 2rem;
            opacity: 0.7;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .stat-label {
            font-size: 0.9rem;
            text-transform: uppercase;
            opacity: 0.8;
        }
        
        .card {
            border: 1px solid var(--border-color);
            border-radius: 10px;
            background: var(--primary-color);
            box-shadow: 0 0 15px rgba(191,161,129,0.04);
            margin-bottom: 30px;
        }
        
        .card-header {
            background-color: var(--primary-color);
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0 !important;
            color: var(--text-color);
        }
        
        .table {
            margin-bottom: 0;
            background-color: var(--primary-color);
            color: var(--text-color);
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--text-color);
            background: var(--primary-color);
        } .table td {
            background: var(--primary-color);
        }
        
        .badge {
            padding: 5px 10px;
            font-weight: 500;
            border-radius: 6px;
            font-size: 0.9em;
        }
        
        .badge-success {
            background-color: var(--badge-success);
            color: var(--text-color);
        }
        
        .badge-warning {
            background-color: var(--badge-warning);
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }
        
        .badge-danger {
            background-color: var(--badge-danger);
            color: #fff;
        }
        
        .badge-secondary {
            background-color: var(--badge-secondary);
            color: var(--text-color);
        }
        
        .btn-action {
            padding: 3px 8px;
            font-size: 0.8rem;
            margin-right: 5px;
            border-radius: 5px;
            border: none;
            background: var(--secondary-color);
            color: var(--text-color);
            transition: background 0.2s;
        }
        
        .btn-action:hover {
            background: var(--primary-color);
            color: var(--text-color);
        }
        
        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(191,161,129,0.08);
            padding: 0.5rem 1rem;
            color: var(--text-color);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--secondary-color);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column">
        <div class="sidebar-header">
            <h4><i class="fas fa-hands-helping me-2"></i> HatidGawa</h4>
            <small>Admin Portal</small>
        </div>
        <div class="sidebar-nav flex-grow-1">
            <a href="dashboard.php"><i class="fas fa-fw fa-tachometer-alt me-2"></i> Dashboard</a>
            <a href="users.php"><i class="fas fa-fw fa-users me-2"></i> Users</a>
            <a href="tasks.php"><i class="fas fa-fw fa-tasks me-2"></i> Tasks</a>
            <a href="safe_zones.php" class="active"><i class="fas fa-fw fa-map-marker-alt me-2"></i> Safezones</a>
            <a href="reports.php"><i class="fas fa-fw fa-flag me-2"></i> Reports</a>
        </div>
        <div class="sidebar-nav">
            <a href="logout.php"><i class="fas fa-fw fa-sign-out-alt me-2"></i> Logout</a>
        </div>
    </div>
    <!-- Main Content -->
    <div class="main-content">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Safe zone added successfully!</div>
        <?php endif; ?>
        <?php if (isset($_GET['edit_success'])): ?>
            <div class="alert alert-success">Safe zone updated successfully!</div>
        <?php endif; ?>
        <?php if (isset($_GET['delete_success'])): ?>
            <div class="alert alert-success">Safe zone deleted successfully!</div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-map-marker-alt me-2"></i> Safe Zones Map
            </div>
            <div class="card-body" style="height: 400px;">
                <div id="all-safezones-map" style="height: 350px; border-radius: 8px; border: 1px solid #ccc;"></div>
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-map-marker-alt me-2"></i> Safe Zones
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSafeZoneModal">
                        <i class="fas fa-plus me-1"></i> Add Safe Zone
                    </button>
                </div>
                <a href="dashboard.php" class="btn btn-sm btn-outline-primary">Back to Dashboard</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Barangay</th>
                                <th>Location Description</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($safezones)): ?>
                                <tr><td colspan="6" class="text-center text-muted">No safe zones found.</td></tr>
                            <?php else: foreach ($safezones as $zone): ?>
                            <tr>
                                <td><?= htmlspecialchars($zone['name']) ?></td>
                                <td><?= htmlspecialchars($zone['barangay']) ?></td>
                                <td><?= htmlspecialchars($zone['location_description']) ?></td>
                                <td>
                                    <?php if ($zone['is_approved']): ?>
                                        <span class="badge badge-success">Approved</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y', strtotime($zone['created_at'])) ?></td>
                                <td>
                                    <?php if (!$zone['is_approved']): ?>
                                        <a href="approve_safezone.php?id=<?= $zone['id'] ?>" class="btn btn-sm btn-action btn-verify" title="Approve"><i class="fas fa-check"></i></a>
                                        <a href="reject_safezone.php?id=<?= $zone['id'] ?>" class="btn btn-sm btn-action btn-ban" title="Reject"><i class="fas fa-times"></i></a>
                                    <?php endif; ?>
                                    <a href="view_safezone.php?id=<?= $zone['id'] ?>" class="btn btn-sm btn-action btn-view" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="#" 
                                       class="btn btn-sm btn-action btn-edit" 
                                       title="Edit"
                                       data-bs-toggle="modal"
                                       data-bs-target="#editSafeZoneModal"
                                       data-id="<?= $zone['id'] ?>"
                                       data-name="<?= htmlspecialchars($zone['name'], ENT_QUOTES) ?>"
                                       data-barangay="<?= htmlspecialchars($zone['barangay'], ENT_QUOTES) ?>"
                                       data-location="<?= htmlspecialchars($zone['location_description'], ENT_QUOTES) ?>"
                                       data-lat="<?= $zone['latitude'] ?>"
                                       data-lng="<?= $zone['longitude'] ?>"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" 
                                       class="btn btn-sm btn-action btn-delete" 
                                       title="Delete"
                                       data-id="<?= $zone['id'] ?>"
                                       data-name="<?= htmlspecialchars($zone['name'], ENT_QUOTES) ?>"
                                       data-bs-toggle="modal"
                                       data-bs-target="#deleteSafeZoneModal"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Add Safe Zone Modal -->
    <div class="modal fade" id="addSafeZoneModal" tabindex="-1" aria-labelledby="addSafeZoneModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <form method="post" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addSafeZoneModalLabel">Add Safe Zone</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="add_safezone" value="1">
            <div class="mb-3">
              <label for="safezone-name" class="form-label">Area/Zone Name</label>
              <input type="text" class="form-control" id="safezone-name" name="name" required>
            </div>
            <div class="mb-3">
              <label for="safezone-barangay" class="form-label">Barangay</label>
              <input type="text" class="form-control" id="safezone-barangay" name="barangay" required>
            </div>
            <div class="mb-3">
              <label for="safezone-location" class="form-label">Location Description</label>
              <textarea class="form-control" id="safezone-location" name="location_description" rows="3" required></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Select Location on Map</label>
              <div id="safezone-map" style="height: 300px; border-radius: 8px; border: 1px solid #ccc;"></div>
              <input type="hidden" id="safezone-lat" name="latitude" required>
              <input type="hidden" id="safezone-lng" name="longitude" required>
              <div class="row mb-2">
                <div class="col">
                  <label class="form-label">Latitude</label>
                  <input type="text" id="safezone-lat-display" class="form-control" readonly>
                </div>
                <div class="col">
                  <label class="form-label">Longitude</label>
                  <input type="text" id="safezone-lng-display" class="form-control" readonly>
                </div>
              </div>
              <div class="form-text">Click on the map to set the safe zone location.</div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Add Safe Zone</button>
          </div>
        </form>
      </div>
    </div>
    <div class="modal fade" id="editSafeZoneModal" tabindex="-1" aria-labelledby="editSafeZoneModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <form method="post" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editSafeZoneModalLabel">Edit Safe Zone</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="edit_safezone" value="1">
            <input type="hidden" id="edit-safezone-id" name="id">
            <input type="hidden" id="edit-safezone-lat" name="latitude">
            <input type="hidden" id="edit-safezone-lng" name="longitude">
            <div class="mb-3">
              <label for="edit-safezone-name" class="form-label">Area/Zone Name</label>
              <input type="text" class="form-control" id="edit-safezone-name" name="name" required>
            </div>
            <div class="mb-3">
              <label for="edit-safezone-barangay" class="form-label">Barangay</label>
              <input type="text" class="form-control" id="edit-safezone-barangay" name="barangay" required>
            </div>
            <div class="mb-3">
              <label for="edit-safezone-location" class="form-label">Location Description</label>
              <textarea class="form-control" id="edit-safezone-location" name="location_description" rows="3" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
    <div class="modal fade" id="deleteSafeZoneModal" tabindex="-1" aria-labelledby="deleteSafeZoneModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <form method="post" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteSafeZoneModalLabel">Delete Safe Zone</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="delete_safezone" value="1">
            <input type="hidden" id="delete-safezone-id" name="id">
            <p>Are you sure you want to delete <strong id="delete-safezone-name"></strong>?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Delete</button>
          </div>
        </form>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
    let map, marker;
    document.addEventListener('DOMContentLoaded', function() {
        var addSafeZoneModal = document.getElementById('addSafeZoneModal');
        addSafeZoneModal.addEventListener('shown.bs.modal', function () {
            // Remove any previous map instance
            if (map) {
                map.remove();
                map = null;
                marker = null;
            }
            // Create a new map instance
            map = L.map('safezone-map').setView([10.93879, 123.42541], 15); // Default: Old Sagay
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            function updateLatLngInputs(lat, lng) {
                document.getElementById('safezone-lat').value = lat;
                document.getElementById('safezone-lng').value = lng;
                if (document.getElementById('safezone-lat-display')) {
                    document.getElementById('safezone-lat-display').value = lat;
                    document.getElementById('safezone-lng-display').value = lng;
                }

                // Reverse geocode to get barangay and area name
                fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                    .then(response => response.json())
                    .then(data => {
                        // Try to get barangay from address
                        let barangay = '';
                        let area = '';
                        if (data.address) {
                            barangay = data.address.suburb || data.address.village || data.address.neighbourhood || data.address.barangay || '';
                            area = data.address.road || data.address.display_name || '';
                        }
                        document.getElementById('safezone-barangay').value = barangay;
                        document.getElementById('safezone-name').value = barangay ? `Safezone - ${barangay}` : area;
                    });
            }

            map.on('click', function(e) {
                const { lat, lng } = e.latlng;
                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng, {draggable: true}).addTo(map);
                    marker.on('dragend', function(ev) {
                        const pos = ev.target.getLatLng();
                        updateLatLngInputs(pos.lat, pos.lng);
                    });
                }
                updateLatLngInputs(lat, lng);
            });

            // Ensure the map is rendered correctly
            setTimeout(() => { map.invalidateSize(); }, 200);
        });

        // Reset map and marker when modal is closed
        addSafeZoneModal.addEventListener('hidden.bs.modal', function () {
            if (map) {
                map.remove();
                map = null;
                marker = null;
            }
            document.getElementById('safezone-lat').value = '';
            document.getElementById('safezone-lng').value = '';
        });

        // Prevent form submit if no location is set
        document.querySelector('#addSafeZoneModal form').addEventListener('submit', function(e) {
            var lat = document.getElementById('safezone-lat').value;
            var lng = document.getElementById('safezone-lng').value;
            if (!lat || !lng) {
                alert('Please select a location on the map.');
                e.preventDefault();
            }
        });

        // Edit modal: fill fields when edit button is clicked
        document.querySelectorAll('.btn-edit').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('edit-safezone-id').value = this.dataset.id;
                document.getElementById('edit-safezone-name').value = this.dataset.name;
                document.getElementById('edit-safezone-barangay').value = this.dataset.barangay;
                document.getElementById('edit-safezone-location').value = this.dataset.location;
                document.getElementById('edit-safezone-lat').value = this.dataset.lat;
                document.getElementById('edit-safezone-lng').value = this.dataset.lng;
            });
        });

        // Delete modal: fill fields when delete button is clicked
        document.querySelectorAll('.btn-delete').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('delete-safezone-id').value = this.dataset.id;
                document.getElementById('delete-safezone-name').textContent = this.dataset.name;
            });
        });

        // --- Safe Zones Overview Map ---
        // PHP will output the safezones array as JSON
        const safezonesData = <?php echo json_encode($safezones); ?>;

        if (document.getElementById('all-safezones-map')) {
            // Center the map at Old Sagay or the first safezone if available
            let centerLat = 10.93879, centerLng = 123.42541, zoom = 14;
            if (safezonesData.length > 0) {
                centerLat = safezonesData[0].latitude || centerLat;
                centerLng = safezonesData[0].longitude || centerLng;
            }
            const allMap = L.map('all-safezones-map').setView([centerLat, centerLng], zoom);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(allMap);

            // Add markers for each safezone
            safezonesData.forEach(function(zone) {
                if (zone.latitude && zone.longitude) {
                    const marker = L.marker([zone.latitude, zone.longitude]).addTo(allMap);
                    marker.bindPopup(
                        `<strong>${zone.name}</strong><br>
                        Barangay: ${zone.barangay}<br>
                        ${zone.location_description}`
                    );
                }
            });

            // Optional: Fit map to markers
            if (safezonesData.length > 0) {
                const bounds = safezonesData
                    .filter(z => z.latitude && z.longitude)
                    .map(z => [z.latitude, z.longitude]);
                if (bounds.length > 0) {
                    allMap.fitBounds(bounds, {padding: [30, 30]});
                }
            }
        }
    });
    </script>
</body>
</html>