<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Complaint</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            200: '#bae2fd',
                            300: '#7cccfd',
                            400: '#36b3f9',
                            500: '#0c9ced',
                            600: '#0281d4',
                            700: '#026aad',
                            800: '#065a8f',
                            900: '#0a4b76'
                        }
                    },
                    animation: {
                        'float': 'float 3s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' }
                        }
                    }
                }
            <?php
            session_start();
            // Redirect if not logged in (external user)
            if(!isset($_SESSION['external_id']) && !isset($_SESSION['user_id'])){ header('Location: ../bpamis_website/login.php'); exit; }
            $external_id = $_SESSION['external_id'] ?? $_SESSION['user_id'];

            include_once '../server/server.php';
            $external_name = '';
            if(isset($conn)){
                if($stmt = $conn->prepare("SELECT First_Name, Middle_Name, Last_Name FROM external_complainant WHERE External_Complaint_ID = ? LIMIT 1")){
                    $stmt->bind_param('i',$external_id);
                    $stmt->execute();
                    $rs = $stmt->get_result();
                    if($rw = $rs->fetch_assoc()){
                        $external_name = trim(implode(' ', array_filter([$rw['First_Name']??'', $rw['Middle_Name']??'', $rw['Last_Name']??''])));
                    }
                    $stmt->close();
                }
            }
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8" />
                <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                <title>Submit Complaint (External)</title>
                <script src="https://cdn.tailwindcss.com"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
                <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
                <script>
                    tailwind.config = { theme:{ extend:{ colors:{ primary:{50:'#f0f7ff',100:'#e0effe',200:'#bae2fd',300:'#7cccfd',400:'#36b3f9',500:'#0c9ced',600:'#0281d4',700:'#026aad',800:'#065a8f',900:'#0a4b76'} }, animation:{'float':'float 8s ease-in-out infinite','fade-in':'fadeIn .45s ease-out'}, keyframes:{ float:{'0%,100%':{transform:'translateY(0)'},'50%':{transform:'translateY(-14px)'}}, fadeIn:{'0%':{opacity:0,transform:'translateY(6px)'},'100%':{opacity:1,transform:'translateY(0)'}} } } } };
                </script>
                <style>
                    .glass { background: linear-gradient(135deg, rgba(255,255,255,0.85), rgba(255,255,255,0.65)); backdrop-filter: blur(12px) saturate(140%); -webkit-backdrop-filter: blur(12px) saturate(140%); }
                    .form-input:focus { border-color:#0c9ced; box-shadow:0 0 0 3px rgba(12,156,237,0.15); outline:none; }
                    /* Make Tagify wrapper match standard input (pl-10 pr-3 py-3 rounded-lg border) */
                    .tagify {
                        border: 1px solid #e5e7eb; /* gray-200 */
                        border-radius: 0.5rem; /* rounded-lg */
                        background:#ffffff;
                        min-height: 3rem; /* matches py-3 vertical space */
                        padding: 0.75rem 0.75rem 0.75rem 2.5rem; /* top/right/bottom/left -> pl-10 pr-3 py-3 */
                        display: flex;
                        align-items: center;
                        font-size: 0.875rem; /* text-sm */
                        line-height:1.25rem;
                        transition: box-shadow .15s, border-color .15s;
                    }
                    .tagify:focus-within {
                        border-color:#0c9ced; /* primary-500 */
                        box-shadow:0 0 0 3px rgba(12,156,237,0.15);
                    }
                    /* Remove internal extra gaps */
                    .tagify__input { margin:0; padding:0; }
                    /* Ensure placeholder styling consistent */
                    .tagify__input::placeholder { color:#9ca3af; /* gray-400 */ }
                    /* Hide default tagify border when empty to rely on our custom border */
                    .tagify__tag { margin-top:0; }
                </style>
            </head>
            <body class="bg-gray-50 font-sans relative overflow-x-hidden">
                <?php include_once('../includes/external_nav.php'); ?>
                <!-- Orbs Background -->
                <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
                    <div class="absolute -top-44 -left-44 w-[500px] h-[500px] bg-blue-200/40 blur-3xl rounded-full animate-[float_14s_ease-in-out_infinite]"></div>
                    <div class="absolute top-1/3 -right-52 w-[560px] h-[560px] bg-cyan-200/40 blur-[160px] rounded-full animate-[float_18s_ease-in-out_infinite]"></div>
                    <div class="absolute -bottom-64 left-1/3 w-[520px] h-[520px] bg-indigo-200/30 blur-3xl rounded-full animate-[float_16s_ease-in-out_infinite]"></div>
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[900px] h-[900px] bg-gradient-to-br from-blue-50 via-white to-cyan-50 opacity-70 blur-[200px] rounded-full"></div>
                </div>

                <!-- Hero -->
                <header class="relative max-w-6xl mx-auto px-4 md:px-8 pt-8 animate-fade-in">
                    <div class="relative glass rounded-2xl shadow-sm border border-white/60 ring-1 ring-primary-100/40 px-6 py-8 md:px-10 md:py-12 overflow-hidden">
                        <div class="absolute -top-10 -right-10 w-48 h-48 rounded-full bg-primary-200/60 blur-2xl"></div>
                        <div class="absolute -bottom-12 -left-12 w-64 h-64 rounded-full bg-primary-300/40 blur-3xl"></div>
                        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                            <div>
                                <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-gray-800 flex items-center gap-3">
                                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-primary-100 text-primary-600 shadow-inner ring-1 ring-white/60"><i class="fa fa-file-circle-plus text-lg"></i></span>
                                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-primary-700 to-primary-500">Submit Complaint</span>
                                </h1>
                                <p class="mt-3 text-sm md:text-base text-gray-600 max-w-prose">File a new complaint for barangay records. Provide necessary details for accurate processing.</p>
                            </div>
                            <div class="flex items-center gap-3 text-xs text-gray-500 flex-wrap">
                                <div class="px-3 py-1 rounded-full bg-white/70 border border-primary-100 flex items-center gap-2"><i class="fa fa-shield-halved text-primary-500"></i> Secure Form</div>
                                <div class="px-3 py-1 rounded-full bg-white/70 border border-primary-100 flex items-center gap-2"><i class="fa fa-user-pen text-primary-500"></i> External Complainant</div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Form Card -->
                <div class="w-full mt-8 px-4 pb-20">
                    <div class="w-full max-w-5xl mx-auto bg-white/95 backdrop-blur-sm rounded-2xl border border-gray-100 shadow-md p-8 md:p-10 relative overflow-hidden">
                        <div class="absolute -top-10 -right-10 w-40 h-40 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-full opacity-70"></div>
                        <div class="absolute -bottom-16 -left-16 w-56 h-56 bg-gradient-to-tr from-blue-50 to-cyan-100 rounded-full opacity-60"></div>
                        <div class="relative z-10">
                            <form action="submit_complaint.php" method="POST" enctype="multipart/form-data" class="space-y-10" id="externalComplaintForm">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">Complainant Name</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-solid fa-user"></i></span>
                                            <input type="text" value="<?= htmlspecialchars($external_name) ?>" disabled class="w-full pl-10 pr-3 py-3 rounded-lg border border-gray-200 bg-gray-50 text-gray-700 focus:outline-none" />
                                            <input type="hidden" name="complainant_name" value="<?= htmlspecialchars($external_name) ?>" />
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="respondent-name" class="block text-sm font-medium text-gray-700">Respondent Name(s) <span class="text-gray-400 font-normal">(Optional)</span></label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 text-gray-400"><i class="fa-solid fa-user-group"></i></span>
                                            <input type="text" id="respondent-name" name="respondent_name" class="w-full pl-10 pr-3 py-3 rounded-lg border border-gray-200 bg-white focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition form-input" placeholder="Type and select respondent names">
                                        </div>
                                        <p class="text-xs text-gray-500 italic">Use full names (First Middle Last). Multiple allowed.</p>
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <label for="incident-date" class="block text-sm font-medium text-gray-700">Incident Date</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-solid fa-calendar-day"></i></span>
                                            <input type="date" id="incident-date" name="incident_date" class="w-full pl-10 pr-3 py-3 rounded-lg border border-gray-200 bg-white focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition form-input">
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="incident-time" class="block text-sm font-medium text-gray-700">Incident Time (Optional)</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-solid fa-clock"></i></span>
                                            <input type="time" id="incident-time" name="incident_time" class="w-full pl-10 pr-3 py-3 rounded-lg border border-gray-200 bg-white focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition form-input">
                                        </div>
                                    </div>
                                    <div class="space-y-2 md:col-span-2">
                                        <label for="complaint-description" class="block text-sm font-medium text-gray-700">Description <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <textarea id="complaint-description" name="complaint_description" rows="6" placeholder="Provide a clear and detailed description..." class="w-full p-4 rounded-lg border border-gray-200 bg-white focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition form-input resize-y" required></textarea>
                                        </div>
                                        <p class="text-xs text-gray-500">Include date, time, location and involved parties if known.</p>
                                    </div>
                                    <input type="hidden" name="out_of_scope" id="out_of_scope" value="0" />
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">Status (Auto)</label>
                                        <div class="px-4 py-3 rounded-lg border border-dashed border-blue-200 bg-blue-50 text-sm text-blue-700 flex items-center gap-2"><i class="fa-solid fa-circle-info"></i> Pending</div>
                                    </div>
                                </div>

                                <!-- Attachments -->
                                <div class="space-y-3">
                                    <label for="complaint-attachment" class="block text-sm font-medium text-gray-700">Attachments <span class="text-gray-400 font-normal">(Optional)</span></label>
                                    <div class="relative">
                                        <label for="complaint-attachment" class="flex flex-col justify-center items-center w-full h-40 bg-gradient-to-br from-gray-50 to-white rounded-xl border border-dashed border-gray-300 cursor-pointer hover:border-blue-300 hover:bg-blue-50/40 transition group">
                                            <div class="flex flex-col justify-center items-center pt-4 pb-5">
                                                <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-3 group-hover:text-blue-500 transition"></i>
                                                <p class="text-sm text-gray-600">Click to upload or drag & drop</p>
                                                <p class="text-xs text-gray-400">PNG, JPG or PDF (max. 5MB each)</p>
                                            </div>
                                            <input id="complaint-attachment" type="file" name="complaint_attachment[]" class="hidden" multiple />
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">You can upload multiple files as evidence for your complaint.</p>
                                </div>

                                <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row gap-3 sm:justify-between items-center">
                                    <div class="text-xs text-gray-500 flex items-start gap-2 max-w-sm"><i class="fa-solid fa-shield-halved text-blue-500 mt-0.5"></i><span>Data recorded here becomes part of the official barangay intake record and is handled confidentially.</span></div>
                                    <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                                        <a href="home-external.php" class="py-3 px-6 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg flex items-center justify-center gap-2 transition"><i class="fa-solid fa-xmark"></i> Cancel</a>
                                        <button type="submit" class="py-3 px-8 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg flex items-center justify-center gap-2 shadow-sm transition"><i class="fa-solid fa-paper-plane"></i> Submit Complaint</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <script>
                // Tagify for respondents (optional multi names)
                const respondentInput = document.getElementById('respondent-name');
                if(respondentInput){ new Tagify(respondentInput,{duplicates:false, dropdown:{enabled:0}}); }
                // File input preview + drag/drop
                document.addEventListener('DOMContentLoaded',()=>{
                    const inputFile=document.getElementById('complaint-attachment');
                    const label=document.querySelector('label[for="complaint-attachment"]');
                    if(inputFile){
                        inputFile.addEventListener('change',()=>{ if(!inputFile.files.length) return; const f=inputFile.files; let html=''; if(f.length===1){ html=`<div class="flex flex-col justify-center items-center pt-4 pb-5"><i class=\"fas fa-file-alt text-primary-500 text-3xl mb-2\"></i><p class=\"text-sm text-gray-700 font-medium\">${f[0].name}</p><p class=\"text-xs text-gray-400 mt-1\">Click to change file</p></div>`;} else { html=`<div class=\"flex flex-col justify-center items-center pt-4 pb-5\"><i class=\"fas fa-file-alt text-primary-500 text-3xl mb-2\"></i><p class=\"text-sm text-gray-700 font-medium\">${f.length} files selected</p><p class=\"text-xs text-gray-400 mt-1\">Click to change files</p></div>`;} label.innerHTML=html; });
                        ['dragenter','dragover','dragleave','drop'].forEach(ev=> label.addEventListener(ev,(e)=>{e.preventDefault();e.stopPropagation();},false));
                        ['dragenter','dragover'].forEach(ev=> label.addEventListener(ev,()=>label.classList.add('border-primary-300','bg-primary-50/50'),false));
                        ['dragleave','drop'].forEach(ev=> label.addEventListener(ev,()=>label.classList.remove('border-primary-300','bg-primary-50/50'),false));
                        label.addEventListener('drop',(e)=>{ inputFile.files=e.dataTransfer.files; inputFile.dispatchEvent(new Event('change',{bubbles:true})); });
                    }
                });
                </script>
                <?php
                    $config = include '../chatbot/config.php';
                    $apiKey = $config['openrouter_api_key'] ?? '';
                ?>
                <script>
                    async function checkComplaintScope(title, description){
                        const prompt = `Determine if the following complaint is within the jurisdiction of a barangay in the Philippines. Respond with "IN_SCOPE" or "OUT_OF_SCOPE".\n\nTitle: ${title}\nDescription: ${description}`;
                        const apiKey = "<?php echo $apiKey; ?>";
                        const response = await fetch("https://openrouter.ai/api/v1/chat/completions", { method:'POST', headers:{ 'Authorization':'Bearer '+apiKey, 'Content-Type':'application/json' }, body: JSON.stringify({ model:'meta-llama/llama-3-8b-instruct', messages:[ { role:'system', content:"You are a legal assistant who determines whether a complaint is within the jurisdiction of the Barangay in the Philippines."+"Barangays only handle minor disputes. Serious crimes are OUT OF SCOPE."+"Always answer only with: IN_SCOPE or OUT_OF_SCOPE"+"The following are examples of OUT OF SCOPE complaints:"+"- Murder (patay, pinatay, saksak, baril, patayan)"+"- Rape (gahasain, panggagahasa)"+"- Illegal drugs (droga, shabu, marijuana)"+"- Major theft/robbery (nanakaw ng kotse, ninakaw ang 15 million pesos, holdap, akyat-bahay)"+"- Any case that involves death, hospital confinement, or firearms" }, { role:'user', content: prompt }] }) });
                        const data = await response.json();
                        return data.choices?.[0]?.message?.content?.trim();
                    }
                </script>
                <!-- Out of Scope Modal -->
                <div id="scope-modal" class="fixed inset-0 bg-black/50 hidden z-50 justify-center items-center">
                    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
                        <h2 class="text-xl font-semibold text-red-600">Possible Out-of-Scope Complaint</h2>
                        <p>This complaint may be outside the jurisdiction of the barangay. Do you still want to submit it?</p>
                        <div class="flex justify-end gap-4 pt-4">
                            <button id="cancel-submit" type="button" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">No</button>
                            <button id="proceed-submit" type="button" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Yes, Submit</button>
                        </div>
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded',()=>{
                        const form=document.getElementById('externalComplaintForm');
                        const modal=document.getElementById('scope-modal');
                        const cancelBtn=document.getElementById('cancel-submit');
                        const proceedBtn=document.getElementById('proceed-submit');
                        let allowed=false;
                        form.addEventListener('submit',async (e)=>{
                            if(allowed) return; e.preventDefault();
                            const title=document.getElementById('complaint-title').value.trim();
                            const desc=document.getElementById('complaint-description').value.trim();
                            const result=await checkComplaintScope(title,desc);
                            if(result==='OUT_OF_SCOPE'){ modal.classList.remove('hidden'); modal.classList.add('flex'); document.getElementById('out_of_scope').value='1'; }
                            else { document.getElementById('out_of_scope').value='0'; allowed=true; form.submit(); }
                        });
                        cancelBtn.addEventListener('click',()=>{ modal.classList.add('hidden'); modal.classList.remove('flex'); });
                        proceedBtn.addEventListener('click',()=>{ allowed=true; modal.classList.add('hidden'); modal.classList.remove('flex'); form.submit(); });
                    });
                </script>
                <?php include '../chatbot/bpamis_case_assistant.php'; ?>
            </body>
            </html>
        $config = include '../chatbot/config.php';
