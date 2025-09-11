<?php
header("Content-Type: application/json");
ini_set('display_errors', 1);
error_reporting(E_ALL);

$config = include 'config.php';
$apiKey = $config['openrouter_api_key'] ?? '';

$input = json_decode(file_get_contents("php://input"), true);
$userMessage = trim(strtolower($input["message"] ?? ""));

if (!$userMessage) {
    echo json_encode(["reply" => "No message received."]);
    exit;
}

$rules = [
    // Lupon and Filing Information
    "how many lupon members" => "According to the Revised KP Law, the lupon is composed of the punong barangay and ten (10) to twenty (20) members, who are residents of the barangay. The lupon shall be constituted every three (3) years.",
    "filing fee" => "The filing fee for barangay complaints ranges from a minimum of five pesos (P5.00) to a maximum of twenty pesos (P20.00).",
    "where can i file" => "You can file a barangay complaint at the Barangay Hall or the Punong Barangay's Office, which is usually located in the center of the barangay, Purok 3 Barangay Panducot Calumpit Bulacan. Alternatively, you can submit your complaint through the BPAMIS system.",

    // General System Functions
    "case status" => "You can view all case statuses in the 'View Cases' section. Would you like me to help you navigate there?",
    "schedule hearing" => "To schedule a new hearing, go to 'Appoint Hearing' under the Schedule menu. To view all upcoming hearings, check the calendar page.",
    "hearing" => "You can view upcoming hearings on the calendar or schedule one via 'Appoint Hearing'.",
    "file complaint" => "To add a new complaint to the system, click on 'Add Complaints' from the menu or use the quick action on the dashboard.",
    "new complaint" => "To file a new complaint, use the 'Add Complaints' section. Fill out the form and submit your issue for processing.",
    "kp forms" => "You can access KP Forms under the 'KP Forms' menu. You can view templates or print pre-filled forms for specific cases.",
    // Generic mediation (will be superseded by more specific timeline rules due to length-based sorting)
    "mediation" => "Mediation in KP is a facilitative step led by the Punong Barangay (or Pangkat later if elevated) to help parties voluntarily settle a dispute before conciliation or arbitration.",
    "mediator" => "A mediator from the Lupon Tagapamayapa will guide the session. You can schedule one via the system.",
    "reports" => "For detailed case reports and statistics, go to 'View Case Reports' or 'View Complaints Report' under the Reports menu.",
    "statistics" => "Visit the Reports menu to view case statistics and complaint summaries.",

    // Barangay Laws / Policies
    "what are your hours" => "Our barangay office is open from 8:00 AM to 5:00 PM, Monday to Friday.",
    "what is a blotter" => "A barangay blotter is a record of complaints, incidents, or disputes filed within the barangay.",
    "lupon tagapamayapa" => "Lupon Tagapamayapa is a group of barangay officials assigned to settle disputes at the barangay level.",

    // Legal Grounds for Complaints
    "valid complaints" => "You can file complaints for minor offenses such as physical injuries, harassment, threats, property disputes, neighborhood disturbances, and other issues involving people within the same barangay.",
    "what to complain" => "Barangays handle complaints like fights, threats, slander, minor theft, noise complaints, trespassing, and family disputes. If unsure, visit the barangay hall for clarification.",
    "barangay jurisdiction" => "Barangays can only mediate cases where both parties live in the same barangay or city. Serious crimes like murder, rape, or drug cases should be filed with the police.",
    
    // Rejection Avoidance
    "why complaint rejected" => "Your complaint may be rejected if it’s not within barangay jurisdiction, lacks complete details, has no supporting evidence, or involves parties outside the barangay.",
    "avoid rejection" => "To avoid rejection, ensure your complaint has complete information (names, dates, incident details), supporting evidence if possible, and involves someone within the same barangay.",
    "how to file properly" => "Visit the barangay hall and fill out the complaint form completely and truthfully. Provide supporting documents if available. Incomplete or dishonest complaints may be rejected.",

    // Police-Level Escalation
    "serious crimes" => "For serious crimes such as murder (patayan), rape, robbery, and drug-related cases, please contact the nearest police station immediately. These are beyond the barangay's jurisdiction.",
    "patayan" => "If the issue involves death, attempted murder, or other serious crimes, this must be reported directly to the police. You may go to the nearest police station or call the local emergency hotline.",
    "not under barangay" => "If the concern involves serious criminal offenses like murder or drug trafficking, it is no longer under barangay jurisdiction. Please report it directly to the police.",
    "emergency hotline" => "For any emergency in Hagonoy, you can call: PNP 0939‑481‑0688, MDRRMO 0930‑035‑6369, BFP 0915‑029‑5184, or national 911.",

    // Administrative timelines (Sections 52, 62, 66)
    "notice of hearing" => "Section 62 timeline: Within 7 days from filing of the administrative complaint, the respondent is required to file a VERIFIED ANSWER within 15 days from receipt. Investigation must start within 10 days after receipt of the answer.",
    "respondent answer" => "Under Section 62, the respondent has 15 days from receipt of the directive to submit a verified answer; the investigation begins within 10 days after that answer is received.",
    "investigation timeline" => "Section 66: Investigation must be terminated within 90 days from its start, and a written decision issued within 30 days after termination, stating facts and reasons.",
    "decision timeline" => "Per Section 66, Decision must be rendered within 30 days after the investigation ends; investigation itself must finish within 90 days from commencement.",
    "sanggunian sessions" => "Section 52: Sanggunian (Province/City/Municipal) meets at least weekly; Sangguniang Barangay meets at least twice a month. Special sessions can be called when public interest demands, with 24-hour written notice for special sessions.",
    "session notice" => "Section 52(d): Special session written notice must be personally served at least 24 hours before it is held; agenda limited to matters stated unless 2/3 of members present agree otherwise.",
    "non compliance notice of hearing" => "Non-compliance with Section 62 (7-day issuance, 15-day answer, 10-day investigation start) or Section 66 (90-day investigation, 30-day decision) may raise due process concerns and grounds for procedural challenge.",
    "case termination timeline" => "Section 66: Investigation terminated within 90 days from start; decision released within 30 days after termination; suspension penalty max 6 months per offense or remaining term; removal bars future elective candidacy.",
    
    // KP Notice of Hearing & Mediation Topics (English) – specific phrases first (will be sorted by length)
    "notice of hearing in kp" => "KP Notice of Hearing (Sec. 410): Issued after complaint filing; includes names of parties, nature of complaint, date/time/place, signed by Punong Barangay.",
    "kp notice of hearing" => "KP Notice of Hearing: Lists complainant, respondent, nature, schedule (date/time/place) and PB signature. Legal basis: Sec. 410.",
    "mediation timeline" => "From filing up to conclusion of mediation/conciliation, the KP process must NOT exceed 45 days.<br><br><strong>Breakdown:</strong><br>• Punong Barangay Mediation: 15 days (extendable ONCE to 30 days total)<br>• Pangkat Conciliation: additional 15 days<br><br><strong>Maximum Total:</strong> 45 days (Sec. 410(b)(c), RA 7160).",
    "mediation duration" => "KP Mediation Timeline: 15 days initial (extendable once to 30) then Pangkat Conciliation adds 15 days. Total ceiling = 45 days (Sec. 410(b)(c)).",
    "timeline for mediation" => "Timeline: 15 days mediation (extendable to 30) + 15 days conciliation = 45-day cap (Sec. 410(b)(c)).",
    "how long is mediation" => "Mediation alone: 15 days (extendable once to make 30). Full KP resolution window including Pangkat conciliation: 45 days max.",
    "total kp duration" => "Total KP process should not exceed 45 days: Mediation 15 (extendable to 30) + Pangkat conciliation 15 (Sec. 410(b)(c)).",
    "non-compliance notice of hearing" => "If complainant absent: possible dismissal + Certificate to Bar Action (bars re‑filing). If respondent absent: Certificate to File Action (CFA) may be issued allowing court filing.",
    
    // Tagalog variants (quick Tagalog responses before model call)
    "paunawa sa pagdinig" => "Paunawa sa Pagdinig (Sek. 410): Pangalan ng mga partido, uri ng reklamo, petsa/oras/lugar ng pagdinig, lagda ng Punong Barangay.",
    "timeline ng mediation" => "Mediation: 15 araw (maaaring palawigin isang beses hanggang 30) + Conciliation (Pangkat) 15 araw; kabuuang limitasyon 45 araw (Sek. 410(b)(c)).",
    "tagal ng mediation" => "Tagal: 15 araw (extendable hanggang 30) para sa mediation; dagdag na 15 para sa conciliation – kabuuang 45 araw max.",
    "ilang araw ang mediation" => "Mediation: 15 araw (pwedeng gawing 30 isang beses lang). Kasama conciliation: total ceiling 45 araw (Sek. 410(b)(c)).",
    "hindi pagdalo sa pagdinig" => "Kung di dumalo ang nagrereklamo: maaaring ibasura + Certificate to Bar Action. Kung di dumalo ang inirereklamo: maaaring maglabas ng CFA (Certificate to File Action).",
    "kabuuang tagal ng kp" => "Kabuuang limitasyon: 45 araw (Mediation 15/30 + Pangkat Conciliation 15). (Sek. 410(b)(c)).",
];


// Match exact or contains keyword with specificity priority (longer keys first)
$ruleKeys = array_keys($rules);
usort($ruleKeys, function($a, $b) {
    return strlen($b) <=> strlen($a); // descending length
});
foreach ($ruleKeys as $key) {
    if (strpos($userMessage, $key) !== false) {
        echo json_encode(["reply" => $rules[$key]]);
        exit;
    }
}

// ---- Knowledge Base Augmentation ----
$kbContext = '';
// Simple Tagalog detection (keyword heuristic)
function is_tagalog_query(string $q): bool {
    $qLow = strtolower($q);
    $markers = [' ang ', ' mga ', ' ito', ' po', ' opo', ' hindi', ' bakit', ' paano', ' saan', ' kailan', 'magkano', 'barangay hall', 'reklamo', 'kasunduan', 'alitan', 'lupon'];
    $hits = 0;
    foreach ($markers as $m) {
        if (strpos($qLow, $m) !== false) { $hits++; }
    }
    // consider Tagalog if at least 2 markers or contains Filipino diacritics (rare) or many words ending with 'ng'
    if ($hits >= 2) return true;
    $ngCount = preg_match_all('/\b\w+ng\b/u', $qLow);
    return $ngCount >= 2;
}
$isTagalog = is_tagalog_query($userMessage);
try {
    require_once __DIR__ . '/knowledge/loader.php';
    $kbMatches = kb_retrieve($userMessage);
    if ($kbMatches) {
        $kbContext = kb_build_context($kbMatches);
    }
} catch (Throwable $e) {
    // Fail silent; log optionally
    // file_put_contents(__DIR__.'/log.txt', "KB ERROR: ".$e->getMessage()."\n", FILE_APPEND);
}

// 🤖 AI Chatbot (if no rule matched)
$systemBase = "You are a helpful assistant for barangay case management in the Philippines. Only respond to questions related to barangay laws, blotters, KP forms, mediation, complaints, hearings, or Lupon Tagapamayapa. If the question is unrelated, say: 'Sorry, I can only help with barangay-related matters.'";
if ($isTagalog) {
    $systemBase .= "\n\nIMPORTANT: The user's query appears to be in Tagalog/Filipino. Respond FULLY in natural Tagalog. Prefer concise Barangay justice terms. If bilingual context is provided (ENGLISH: ... TAGALOG: ...), extract and use ONLY the TAGALOG portions in the answer. Do NOT translate back to English unless the user asks. Retain legal references (RA 7160 sections) verbatim.";
} else {
    $systemBase .= "\nIf the user switches to Tagalog later, switch your replies to Tagalog using the TAGALOG sections of the context.";
}
if ($kbContext) {
    $systemBase .= "\n\nUse the following verified barangay knowledge base context if relevant. When context includes both ENGLISH and TAGALOG sections, select the appropriate language (Tagalog if detected). Cite only facts present there when possible.\n".$kbContext;
}

$messages = [
    [ 'role' => 'system', 'content' => $systemBase ],
    [ 'role' => 'user', 'content' => $userMessage ]
];

$data = [
    "model" => "meta-llama/llama-3-8b-instruct", // Replace with other model if needed
    "messages" => $messages,
    "temperature" => 0.5,
    "max_tokens" => 500
];

$ch = curl_init("https://openrouter.ai/api/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $apiKey",
    "X-Title: BarangayBot"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
file_put_contents("log.txt", $response); // For debugging

if (curl_errno($ch)) {
    echo json_encode(["reply" => "Curl Error: " . curl_error($ch)]);
    exit;
}

curl_close($ch);

$responseData = json_decode($response, true);
if (isset($responseData['error'])) {
    echo json_encode(["reply" => "OpenRouter Error: " . $responseData['error']['message']]);
} else {
    $botReply = $responseData["choices"][0]["message"]["content"] ?? "Sorry, I couldn’t generate a response.";
    echo json_encode(["reply" => $botReply]);
}
