<?php
/**
 * View Hearing Calendar Page
 * Barangay Panducot Adjudication Management Information System
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Hearing Calendar</title>

    <!-- FullCalendar -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Font Awesome for Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    
    <!-- Case Assistant Styles -->
    <?php include '../includes/case_assistant_styles.php'; ?>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
        }
        #calendar {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            background: white;
        }
        .fc-event {
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-blue-50">

    <!-- Navigation -->
    <?php include '../includes/barangay_official_nav.php'; ?>

    <!-- Calendar Container -->
    <div class="container mx-auto mt-10 p-5 bg-white shadow-md rounded-lg">
        <h2 class="text-2xl font-semibold text-blue-800 mb-4">Hearing Calendar</h2>
        
        <div class="mb-4 flex justify-between">
            <div>
                <a href="appoint_hearing.php" class="bg-green-500 text-white p-2 px-4 rounded-lg hover:bg-green-600">
                    <i class="fas fa-plus"></i> Schedule New Hearing
                </a>
            </div>
            <div>
                <a href="reschedule_hearing.php" class="bg-yellow-500 text-white p-2 px-4 rounded-lg hover:bg-yellow-600">
                    <i class="fas fa-calendar-alt"></i> Reschedule Hearing
                </a>
            </div>
        </div>
        
        <div id="calendar"></div>
    </div>

    <!-- Modal for Adding Event -->
    <div class="modal fade" id="addEventModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Schedule Hearing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="caseSelect" class="form-label">Select Case</label>
                        <select id="caseSelect" class="form-control">
                            <option value="">-- Select Case --</option>
                            <?php
                            // In a real app, you'd fetch this from the database
                            $cases = [
                                ['id' => 'KP-2025-001', 'title' => 'Property Boundary Dispute'],
                                ['id' => 'KP-2025-002', 'title' => 'Unpaid Debt'],
                                ['id' => 'KP-2025-003', 'title' => 'Noise Complaint']
                            ];
                            
                            foreach ($cases as $case) {
                                echo '<option value="' . $case['id'] . '">' . $case['id'] . ' - ' . $case['title'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="eventTitle" class="form-label">Hearing Title</label>
                        <input type="text" id="eventTitle" class="form-control" placeholder="Enter hearing title">
                    </div>
                    <div class="mb-3">
                        <label for="eventStart" class="form-label">Start Time</label>
                        <input type="datetime-local" id="eventStart" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="eventEnd" class="form-label">End Time</label>
                        <input type="datetime-local" id="eventEnd" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="eventVenue" class="form-label">Venue</label>
                        <input type="text" id="eventVenue" class="form-control" placeholder="Enter venue">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveEvent">Schedule Hearing</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Editing Event -->
    <div class="modal fade" id="editEventModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">Edit Hearing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editEventCase" class="form-label">Case</label>
                        <input type="text" id="editEventCase" class="form-control" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="editEventTitle" class="form-label">Hearing Title</label>
                        <input type="text" id="editEventTitle" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="editEventStart" class="form-label">Start Time</label>
                        <input type="datetime-local" id="editEventStart" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="editEventEnd" class="form-label">End Time</label>
                        <input type="datetime-local" id="editEventEnd" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="editEventVenue" class="form-label">Venue</label>
                        <input type="text" id="editEventVenue" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="editEventStatus" class="form-label">Status</label>
                        <select id="editEventStatus" class="form-control">
                            <option value="scheduled">Scheduled</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="rescheduled">Rescheduled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="deleteEvent">Delete</button>
                    <button type="button" class="btn btn-success" id="updateEvent">Update</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let selectedEvent = null;
            let selectedStart, selectedEnd;
            
            // In a real application, this would be fetched from the database via AJAX
            // For now, we'll use sample data
            <?php
            // Current date for dynamic sample data
            $currentDate = date('Y-m-d');
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            $dayAfterTomorrow = date('Y-m-d', strtotime('+2 days'));
            ?>

            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                editable: true,
                selectable: true,
                selectMirror: true,
                events: [
                    { 
                        id: '1', 
                        title: 'Property Boundary Dispute Hearing', 
                        start: '<?php echo $currentDate; ?>T10:00:00', 
                        end: '<?php echo $currentDate; ?>T12:00:00',
                        extendedProps: {
                            case: 'KP-2025-001',
                            venue: 'Barangay Hall - Conference Room 1'
                        }
                    },
                    { 
                        id: '2', 
                        title: 'Unpaid Debt Mediation', 
                        start: '<?php echo $tomorrow; ?>T14:00:00', 
                        end: '<?php echo $tomorrow; ?>T16:00:00',
                        extendedProps: {
                            case: 'KP-2025-002',
                            venue: 'Barangay Hall - Mediation Room'
                        }
                    },
                    { 
                        id: '3', 
                        title: 'Noise Complaint Hearing', 
                        start: '<?php echo $dayAfterTomorrow; ?>T09:00:00', 
                        end: '<?php echo $dayAfterTomorrow; ?>T11:00:00',
                        extendedProps: {
                            case: 'KP-2025-003',
                            venue: 'Barangay Hall - Conference Room 2'
                        }
                    }
                ],

                // Show Bootstrap modal when selecting a date
                select: function(info) {
                    selectedStart = info.startStr;
                    selectedEnd = info.endStr;
                    document.getElementById('eventTitle').value = "";
                    document.getElementById('eventStart').value = selectedStart.substring(0, 16); // Format for datetime-local
                    document.getElementById('eventEnd').value = selectedEnd.substring(0, 16); // Format for datetime-local
                    new bootstrap.Modal(document.getElementById('addEventModal')).show();
                },

                // Show Bootstrap modal when clicking an event
                eventClick: function(info) {
                    selectedEvent = info.event;
                    document.getElementById('editEventCase').value = selectedEvent.extendedProps?.case || '';
                    document.getElementById('editEventTitle').value = selectedEvent.title;
                    document.getElementById('editEventStart').value = selectedEvent.start ? selectedEvent.start.toISOString().substring(0, 16) : '';
                    document.getElementById('editEventEnd').value = selectedEvent.end ? selectedEvent.end.toISOString().substring(0, 16) : '';
                    document.getElementById('editEventVenue').value = selectedEvent.extendedProps?.venue || '';
                    new bootstrap.Modal(document.getElementById('editEventModal')).show();
                }
            });

            calendar.render();

            // Handle event addition
            document.getElementById('saveEvent').addEventListener('click', function() {
                let caseId = document.getElementById('caseSelect').value;
                let eventTitle = document.getElementById('eventTitle').value.trim();
                let start = document.getElementById('eventStart').value;
                let end = document.getElementById('eventEnd').value;
                let venue = document.getElementById('eventVenue').value.trim();
                
                if (eventTitle && start && end) {
                    calendar.addEvent({
                        id: String(Date.now()),
                        title: eventTitle,
                        start: start,
                        end: end,
                        allDay: false,
                        extendedProps: {
                            case: caseId,
                            venue: venue
                        }
                    });
                    bootstrap.Modal.getInstance(document.getElementById('addEventModal')).hide();
                    
                    // In a real application, you would save this to the database via AJAX
                    // For now, we'll just show an alert
                    alert("Hearing scheduled successfully!");
                } else {
                    alert("Please enter all required fields.");
                }
            });

            // Handle event update
            document.getElementById('updateEvent').addEventListener('click', function() {
                if (selectedEvent) {
                    selectedEvent.setProp('title', document.getElementById('editEventTitle').value.trim());
                    selectedEvent.setStart(document.getElementById('editEventStart').value);
                    selectedEvent.setEnd(document.getElementById('editEventEnd').value);
                    
                    // Update extended properties
                    selectedEvent.setExtendedProp('venue', document.getElementById('editEventVenue').value);
                    
                    bootstrap.Modal.getInstance(document.getElementById('editEventModal')).hide();
                    
                    // In a real application, you would update this in the database via AJAX
                    alert("Hearing details updated successfully!");
                }
            });

            // Handle event deletion
            document.getElementById('deleteEvent').addEventListener('click', function() {
                if (selectedEvent) {
                    if (confirm('Are you sure you want to delete this hearing?')) {
                        selectedEvent.remove();
                        bootstrap.Modal.getInstance(document.getElementById('editEventModal')).hide();
                        
                        // In a real application, you would delete this from the database via AJAX
                        alert("Hearing deleted successfully!");
                    }
                }
            });        });
    </script>

    <?php include '../includes/case_assistant.php'; ?>
</body>
</html>
