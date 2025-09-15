<?php
require_once('db-connect.php');

session_start();


// Default values
$lupon_name = '';
$notif_count = 0;

// Check if official is logged in
if (!empty($_SESSION['official_id'])) {
    $luponId = $_SESSION['official_id'];

    // Get lupon name from DB
    $sqlLupon = "SELECT name AS lupon_name FROM barangay_officials WHERE official_id = ?";
    if ($stmt = $conn->prepare($sqlLupon)) {
        $stmt->bind_param("i", $luponId);
        $stmt->execute();
        $resultLupon = $stmt->get_result();
        if ($resultLupon && $row = $resultLupon->fetch_assoc()) {
            $_SESSION['lupon_name'] = $row['lupon_name'];
            $lupon_name = $row['lupon_name'];
        }
        $stmt->close();
    }

}



?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scheduling</title>
  <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" crossorigin="anonymous" />
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="./fullcalendar/lib/main.min.css" rel="stylesheet" />
  <link href="./SecMenu/schedule/fullcalendar/lib/main.css" rel="stylesheet" />
  <script src="./js/jquery-3.6.0.min.js"></script>
  <script src="./fullcalendar/lib/main.min.js"></script>
  <script src="./fullcalendar/lib/main.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
  <style>
    html, body { height:100%; }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin:0;
      padding:0;
      display:flex;
      flex-direction:column;
    }
    /* Modern glassmorphism calendar container */
    #calendar-wrapper { flex:1 1 auto; display:flex; padding:0.8rem 0.8rem 0.5rem 0.5rem; }
    #calendar {
      width:100%;
      height:100%;
      background:rgba(255,255,255,0.7);
      box-shadow:0 6px 28px -10px rgba(12,110,175,0.25),0 2px 10px -4px rgba(12,110,175,0.18);
      border-radius:20px;
      border:1px solid rgba(180,205,225,0.55);
      padding:1.25rem 1rem .75rem 1rem;
      transition:box-shadow .3s, transform .3s;
      backdrop-filter:blur(10px);
    }
    #calendar:hover { transform: translateY(-4px) scale(1.01); }
    .fc .fc-toolbar-title {
      font-weight: 700;
      font-size: 1.15rem;
      color: #0281d4;
      letter-spacing: 0.5px;
      display: flex;
      align-items: center;
      gap: 0.4em;
    }
    .fc .fc-button {
      box-shadow: none !important;
      padding: 0.32rem 0.60rem;
      border-radius: 7px !important;
      font-weight: 600;
      font-size: 0.78rem;
      line-height: 1;
      transition: all 0.18s;
      text-transform: capitalize;
      border: 1px solid #b6e1f7 !important;
      background: linear-gradient(120deg, #e8f7ff 0%, #f3fbff 100%) !important;
      color: #0276c4 !important;
      margin: 0 0.15rem;
      display: flex;
      align-items: center;
      gap: 0.25em;
      min-height: 30px;
    }
    .fc .fc-button .fc-icon { font-size: 0.9rem; }
    /* Compact arrow buttons */
    .fc .fc-prev-button, .fc .fc-next-button { padding: 0.25rem 0.5rem !important; width: 32px; }
    .fc .fc-button-primary:hover {
      background: linear-gradient(120deg, #d5eefc 0%, #e8f7ff 100%) !important;
      color: #0369a1 !important;
      transform: translateY(-2px);
    }
    .fc .fc-button-primary:not(:disabled).fc-button-active, 
    .fc .fc-button-primary:not(:disabled):active {
      background: #e4f6ff !important;
      color: #0b6fa2 !important;
      box-shadow: inset 0 0 0 1px #9ed3ed;
    }
    .fc .fc-daygrid-day-number {
      padding: 12px;
      font-size: 1rem;
      color: #0281d4;
      font-weight: 600;
    }
    .fc .fc-daygrid-day.fc-day-today {
      background: linear-gradient(90deg,rgba(199, 229, 248, 0.85) 0%, #f0f9ff 100%) !important;
      border-radius: 12px;
      box-shadow: 0 2px 8px 0 rgba(2, 129, 212, 0.08);
    }
    .fc .fc-event {
      border: none !important;
      padding: 10px 18px 10px 16px;
      font-size: 1.08rem !important;
      font-weight: 700;
      margin-top: 6px;
      border-radius: 14px !important;
      background: rgba(214, 214, 214, 0.28) !important;
      color: #1e293b !important;
      box-shadow: 0 4px 16px 0 rgba(2, 129, 212, 0.13);
      display: flex;
      align-items: center;
      gap: 0.7em;
      position: relative;
      transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
      backdrop-filter: blur(2px);
      overflow: hidden;
      animation: eventFadeIn 0.5s;
      line-height: 1.3;
      white-space: nowrap;
      text-overflow: ellipsis;
    }
    .fc .fc-event:hover, .fc .fc-event:focus {
      transform: scale(1.06) translateY(-3px);
      box-shadow: 0 10px 28px 0 rgba(2, 129, 212, 0.22);
      z-index: 2;
      background: rgba(255,255,255,0.38) !important;
    }
    @keyframes eventFadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    /* Modal glassmorphism style */
    #event-details-modal .bg-white {
      background: rgba(255,255,255,0.95);
      box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.13);
      border-radius: 22px;
      border: 1.5px solid #e0e7ef;
      backdrop-filter: blur(8px);
    }
    #event-details-modal h5 {
      color: #0281d4;
      font-weight: 700;
      font-size: 1.3rem;
    }
    #event-details-modal dt {
      color: #0281d4;
      font-weight: 600;
    }
    #event-details-modal dd {
      font-size: 1.08rem;
    }
    #event-details-modal button {
      transition: background 0.2s, color 0.2s, box-shadow 0.2s;
    }
    #event-details-modal button:focus {
      outline: 2px solid #0281d4;
      outline-offset: 2px;
    }
    /* Responsive tweaks */
    @media (max-width: 640px) {
      #calendar {
        padding: 0.5rem 0.1rem 0.5rem 0.1rem;
        min-width: 0;
        width: 100%;
        box-sizing: border-box;
      }
      .fc .fc-toolbar-title { font-size: 0.95rem; }
      .fc .fc-button { font-size: 0.7rem; padding: 0.28rem 0.5rem; }
      .fc .fc-daygrid-day-number {
        font-size: 0.92rem;
        padding: 6px;
      }
      .fc .fc-event {
        font-size: 0.95rem !important;
        padding: 8px 4px 8px 8px;
        flex-direction: row;
        align-items: center;
        min-width: 0;
        max-width: 100%;
        white-space: nowrap;
        text-overflow: ellipsis;
      }
      .fc .fc-event-title {
        color: #1e293b !important;
        font-size: 0.95rem;
      }
    }
    /* Ensure event time and title are black in week and list views */
    .fc-timeGridWeek-view .fc-event-time, 
    .fc-timeGridWeek-view .fc-event-title,
    .fc-listWeek-view .fc-event-time, 
    .fc-listWeek-view .fc-event-title { color: #111 !important; }
    .fc-list .fc-event-time, .fc-list .fc-event-title { color: #111 !important; }
    /* Highlight current day in week list view */
    .fc-listWeek-view .fc-list-day.fc-day-today {
      background: linear-gradient(90deg,rgba(199, 229, 248, 0.85) 0%, #f0f9ff 100%) !important;
    }
    /* Highlight current day column in timeGrid views */
    .fc-timeGridWeek-view .fc-col-today,
    .fc-timeGridDay-view .fc-col-today,
    .fc-timeGridWeek-view .fc-timegrid-col.fc-day-today,
    .fc-timeGridDay-view .fc-timegrid-col.fc-day-today,
    .fc-timeGridWeek-view .fc-timegrid-slot-label.fc-day-today,
    .fc-timeGridDay-view .fc-timegrid-slot-label.fc-day-today {
      background: linear-gradient(90deg,rgba(199, 229, 248, 0.85) 0%, #f0f9ff 100%) !important;
    }
  </style>
</head>

<body>
  <div id="calendar-wrapper">
    <div id="calendar" class="relative z-10"></div>
  </div>

  <!-- Event Details Modal -->
  <div class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center hidden" id="event-details-modal">
    <div class="bg-white rounded-lg w-full max-w-md">
      <div class="border-b px-6 py-4 flex justify-between items-center">
        <h5 class="text-lg font-semibold">Schedule Details</h5>
        <button class="text-gray-500 hover:text-gray-800" onclick="closeModal()">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="px-6 py-4 space-y-3">
      <div>
          <dt class="text-gray-500 text-sm">Case Number</dt>
          <dd id="case-id" class="text-xl font-bold"></dd>
        </div>
        <div>
          <dt class="text-gray-500 text-sm">Title</dt>
          <dd id="title" class="text-xl font-bold"></dd>
        </div>
        <div>
          <dt class="text-gray-500 text-sm">Date & Time</dt>
          <dd id="start" class="text-gray-700"></dd>
        </div>
        <div>
          <dt class="text-gray-500 text-sm">View Details</dt>
          <dd>
            <a id="view-details-link" href="#" target="_blank" class="text-gray-500 text-sm underline">Go to Case Info</a>
          </dd>
        </div>
      </div>
      <div class="border-t px-6 py-4 flex justify-end space-x-2">
       
        <button onclick="closeModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded text-sm">Back</button>
      </div>
    </div>
  </div>

 <?php 

// Prepare the query to get hearings for this lupon
$sql = "
    SELECT sl.*
    FROM schedule_list sl
    INNER JOIN case_info ci ON ci.case_id = sl.case_id
    LEFT JOIN mediation_info mi 
        ON mi.case_id = ci.case_id AND mi.mediator_name = ?
    LEFT JOIN resolution r 
        ON r.case_id = ci.case_id AND r.mediator_name = ?
    LEFT JOIN settlement s 
        ON s.case_id = ci.case_id AND s.mediator_name = ?
    WHERE mi.case_id IS NOT NULL 
       OR r.case_id IS NOT NULL 
       OR s.case_id IS NOT NULL
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $lupon_name, $lupon_name, $lupon_name);
$stmt->execute();
$result = $stmt->get_result();

$sched_res = [];
while ($row = $result->fetch_assoc()) {
    $row['sdate'] = date("F d, Y h:i A", strtotime($row['hearingDateTime']));
    $row['edate'] = date("F d, Y h:i A", strtotime($row['hearingDateTime'] . ' +1 hour'));
    $row['title'] = $row['hearingTitle'];
    $row['description'] = $row['remarks'];
    $sched_res[$row['hearingID']] = $row;
}

$stmt->close();
if (isset($conn)) $conn->close();

?>

  
  <script>
    var scheds = <?= json_encode($sched_res) ?>;
  </script>

  <script>
    var calendar;
    var Calendar = FullCalendar.Calendar;
    var events = [];

    $(function () {
      if (!!scheds) {
        Object.keys(scheds).map(k => {
          var row = scheds[k];
          events.push({
            id: k,
            title: row.title,
            start: row.hearingDateTime,
            end: moment(row.hearingDateTime).add(1, 'hour').format('YYYY-MM-DDTHH:mm:ss')
          });
        });
      }

      calendar = new Calendar(document.getElementById('calendar'), {
        headerToolbar: {
          left: 'prev,next',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        height: '100%',
        expandRows: true,
        selectable: true,
        themeSystem: 'standard',
        events: events,
        initialView: 'dayGridMonth',
        eventTimeFormat: { // Show AM/PM in all views
          hour: 'numeric',
          minute: '2-digit',
          meridiem: 'short'
        },
        eventContent: function(arg) {
          // Only customize for week and list views
          var viewType = arg.view.type;
          if (viewType === 'timeGridWeek' || viewType === 'listWeek' || viewType === 'listMonth' || viewType === 'listDay') {
            // Get the case number from scheds
            var caseNum = '';
            if (scheds && scheds[arg.event.id] && scheds[arg.event.id].Case_ID) {
              caseNum = 'Case#' + scheds[arg.event.id].Case_ID;
            } else {
              caseNum = 'Case#' + arg.event.id;
            }
            return { html: '<span style="font-weight:700;color:#111;">' + caseNum + '</span>' };
          }
          // Default rendering for other views
        },
        eventClick: function (info) {
          let event = scheds[info.event.id];
          $('#case-id').text(event.Case_ID);
          $('#title').text(event.title);
          $('#start').text(event.sdate);
          // Set the view details link
          $('#view-details-link').attr('href', '../view_case_details.php?id=' + event.Case_ID);
          $('#event-details-modal').removeClass('hidden');
        },
        editable: false
      });

      calendar.render();
      // Ensure full height after initial paint
      setTimeout(()=>calendar.updateSize(),50);

    });

    function closeModal() {
      $('#event-details-modal').addClass('hidden');
    }
  </script>
</body>

</html>
