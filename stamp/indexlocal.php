<?php 
include 'db.php';

// ================== ANTI CACHE (HALAMAN) ==================
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");


                $_SESSION['user_id']   = 1;
                $_SESSION['user_name'] = "admin";
                $_SESSION['role']      = "admin";
				
// --- PROTEKSI LOGIN ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId   = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$userRole = $_SESSION['role'] ?? 'user';

// --- CEK HAK STAMP (admin atau user tertentu) ---
$canStamp = false;
/*
$qUser = $conn->query("SELECT can_stamp, role FROM users WHERE id = '$userId' LIMIT 1");
if ($qUser && ($urow = $qUser->fetch_assoc())) {
    $canStamp = ($urow['role'] === 'admin') || ((int)$urow['can_stamp'] === 1);
} else {
    $canStamp = ($userRole === 'admin');
}
*/
if($userRole=="admin"){ $canStamp = true; }

/*
// --- AMBIL ID PDF DARI URL ---
$pdf_id = isset($_GET['pdf_id']) ? intval($_GET['pdf_id']) : 0;
$stamp = isset($_GET['stamp']) ? intval($_GET['stamp']) : 0;

$res = $conn->query("SELECT * FROM list_pdf WHERE id = '$pdf_id' LIMIT 1");
$pdfData = $res ? $res->fetch_assoc() : null;
if (!$pdfData) die("Error: Data PDF tidak ditemukan.");

$fileName = $pdfData['file_name'];
*/

$pdf_id = isset($_GET['pdf_id']) ? intval($_GET['pdf_id']) : 0;
$stamp = isset($_GET['stamp']) ? intval($_GET['stamp']) : 0;
$fileName = isset($_GET['fileName']) ? $_GET['fileName'] : '';



// ===== PATH KERJA (TETAP) =====
$workPath = "storage/" . $pdf_id . "/" . $fileName;

// ===== PATH APPROVE (STAMP) =====
$approvePath = "storage/" . $pdf_id . "/approve/APPROVED_" . $fileName;

// Work wajib ada untuk preview stamp
if (!file_exists($workPath)) die("Error: File kerja tidak ditemukan di: $workPath");

$viewPath = $workPath;
if ($stamp == 1) {
    $viewPath = file_exists($approvePath) ? $approvePath : $workPath;
}

// URL web anti-cache
$WORK_URL = $workPath . "?v=" . @filemtime($workPath);                 
$VIEW_URL = $viewPath . "?v=" . @filemtime($viewPath);                 
$VIEW_NAME_NOQUERY = $viewPath;                                        
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';       

//echo $VIEW_URL;
// echo $WORK_URL;
//echo "<pre>DEBUG VIEW_URL (untuk Adobe): " . htmlspecialchars($VIEW_URL) . "</pre>";
//DEBUG VIEW_URL (untuk Adobe): storage/5/support.pdf?v=1772074155




// $pdf_id="5";
// $fileName="support.pdf";

/*
$workPath = "uploads/document/" . $pdf_id . "/" . $fileName ;
$approvePath = "storage/" . $pdf_id . "/approve/APPROVED_" . $fileName;
$viewPath = $workPath;
if ($stamp == 1) {
    $viewPath = file_exists($approvePath) ? $approvePath : $workPath;
}
$baseUrlUpload = "/storage/uploads/document/";  // pakai symlink storage
$workUrl = $baseUrlUpload . $pdf_id . "/" . $fileName;
$approveUrl = $baseUrlUpload . $pdf_id . "/approve/APPROVED_" . $fileName;
$VIEW_URL = ($stamp == 1 ? $approveUrl : $workUrl);

// URL web anti-cache
$WORK_URL = $workPath;                                 
$VIEW_NAME_NOQUERY = $viewPath;                                        
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';    

*/






echo $WORK_URL;






?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kolaborasi PDF - Multi Reviewer</title>
  <style>
    html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; font-family: 'Segoe UI', sans-serif; }
    .main-wrapper { display: flex; flex-direction: column; height: 100vh; }
    .top-bar {
      display: flex; justify-content: space-between; align-items: center;
      padding: 10px 20px; background: #2c3e50; color: white; border-bottom: 1px solid #ddd;
    }
    .btn-save { background-color: #27ae60; color: white; border: none; padding: 10px 20px; font-weight: bold; border-radius: 4px; cursor: pointer; }
    .btn-download { background-color: #2980b9; color: white; border: none; padding: 10px 20px; font-weight: bold; border-radius: 4px; cursor: pointer; margin-left: 10px; text-decoration:none; display:inline-block; }
    #adobe-dc-view { flex-grow: 1; width: 100%; }
    #sync-info { font-size: 12px; color: #bdc3c7; margin-right: 15px; }
    .user-badge {
      font-size: 13px; background: #34495e; padding: 6px 12px; border-radius: 4px;
      margin-right: 15px; border: 1px solid #5d6d7e; cursor: pointer;
    }
  </style>
</head>
<body>

<div class="main-wrapper">
  <div class="top-bar">
    <h2 style="margin:0;font-size:18px;">📝 Kolaborasi PDF</h2>
    <div>
      <span class="user-badge" onclick="changeName()">👤 <?=htmlspecialchars($userName)?></span>
      <span id="sync-info">Status: Menunggu...</span>
      <?php if($stamp!=1){ ?>
      <button class="btn-save" onclick="saveToDatabase()">💾 SIMPAN</button>
      <?php } ?>
      <?php if ($canStamp): ?>
        <button class="btn-download" onclick="openStampModal()">🧾 STAMP</button>
      <?php endif; ?>
      <a class="btn-download" href="dashboard.php">Home</a>
    </div>
  </div>

  <div id="adobe-dc-view"></div>
</div>

<?php if ($canStamp): ?>
<!-- ====== STAMP MODAL ====== -->
<div id="stamp-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:9999; align-items:center; justify-content:center;">
  <div style="background:#fff; width:min(980px,96vw); border-radius:10px; overflow:hidden; box-shadow:0 10px 40px rgba(0,0,0,.25);">
    <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 16px; background:#2c3e50; color:#fff;">
      <div style="font-weight:700;">🧾 Atur Stamp (Drag, Resize) → Apply</div>
      <button onclick="closeStampModal()" style="background:transparent; border:1px solid rgba(255,255,255,.5); color:#fff; padding:6px 10px; border-radius:6px; cursor:pointer;">Tutup</button>
    </div>

    <div style="display:flex; gap:12px; padding:14px;">
      <div style="width:260px; border-right:1px solid #eee; padding-right:12px;">
        <label style="display:block; font-size:12px; color:#555; margin-bottom:6px;">Tipe stamp</label>
        <select id="stamp-type" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:6px;">
          <option value="company_logo">Company Logo</option>
          <option value="vendor_stamp">Vendor Stamp</option>
          <option value="company_stamp">Company Stamp</option>
          <option value="approved_without_comment">Approved Without Comment</option>
          <option value="approved_with_comments">Approved With Comments</option>
          <option value="rejected_due_to_error">Rejected Due To Error</option>
        </select>

        <label style="display:block; font-size:12px; color:#555; margin:12px 0 6px;">Halaman</label>
        <div style="display:flex; gap:8px; align-items:center;">
          <input id="stamp-page" type="number" min="1" value="1" style="flex:1; padding:8px; border:1px solid #ddd; border-radius:6px;" />
          <button onclick="renderStampPreview()" style="padding:8px 10px; border:1px solid #ddd; border-radius:6px; cursor:pointer; background:#f7f7f7;">Load</button>
        </div>

        <div style="display:flex; gap:8px; margin-top:16px;">
          <button onclick="applyStampToPdf()" style="flex:1; padding:10px; border:0; border-radius:6px; cursor:pointer; background:#27ae60; color:#fff; font-weight:700;">Apply</button>
          <button onclick="resetStampBox()" style="padding:10px; border:1px solid #ddd; border-radius:6px; cursor:pointer; background:#fff;">Reset</button>
        </div>

        <div id="stamp-status" style="margin-top:10px; font-size:12px; color:#444;"></div>
      </div>

      <div style="flex:1;">
        <div id="stamp-preview-wrap" style="position:relative; width:100%; height:520px; background:#fafafa; border:1px solid #eee; border-radius:10px; overflow:auto;">
          <canvas id="stamp-canvas" style="display:block; margin:14px auto;"></canvas>

          <div id="stamp-box"
               style="display:none; position:absolute; border:2px dashed #2980b9; border-radius:6px;
                      cursor:move; z-index:20; touch-action:none; user-select:none;">
            <img id="stamp-img" src="" alt="stamp" style="width:100%; height:100%; display:block; pointer-events:none;" />
            <div style="position:absolute; right:-8px; bottom:-8px; width:14px; height:14px; background:#2980b9; border-radius:50%; cursor:nwse-resize;"></div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<?php endif; ?>

<script src="https://documentservices.adobe.com/view-sdk/viewer.js"></script>

<?php if ($canStamp): ?>
<script src="assets/vendor/pdfjs/pdf.min.js?v=1"></script>
<script src="assets/vendor/interact/interact.min.js?v=1"></script>
<?php endif; ?>

<script>
const CURRENT_USER = {
  id: "<?= (string)$userId ?>",
  name: "<?= htmlspecialchars($userName, ENT_QUOTES) ?>"
};

const ADOBE_KEY  = "6e754bf0041944e7b665e1d8c4b7f741";
const BASE_URL   = "<?= $baseUrl ?>";

const WORK_URL   = "<?= $WORK_URL ?>";
const VIEW_URL   = "<?= $VIEW_URL ?>";
const VIEW_NAME  = "<?= $VIEW_NAME_NOQUERY ?>";

const FILE_ID    = "pdf_<?= (int)$pdf_id ?>";
const PDF_ID_NUM = <?= (int)$pdf_id ?>;

const IS_STAMP_VIEW = <?= ($stamp==1 ? 'true':'false') ?>;

let globalAnnotManager;
let loadedIds = new Set();

function stringToColor(str) {
  let hash = 0;
  for (let i=0;i<str.length;i++) hash = str.charCodeAt(i) + ((hash<<5)-hash);
  let color = '#';
  for (let i=0;i<3;i++){
    let v = (hash >> (i*8)) & 0xFF;
    color += ('00'+v.toString(16)).substr(-2);
  }
  return color;
}

// ================ ADOBE INIT ================
//content: { location: { url: window.location.origin + BASE_URL + VIEW_URL } },
//http://dzaries.my.id:8000/uploads/document/5/support.pdf
//'http://dzaries.my.id/edmg-github-clone/edmg/public/uploads/document/5/support.pdf' 
document.addEventListener("adobe_dc_view_sdk.ready", function () {
  const adobeDCView = new AdobeDC.View({ clientId: ADOBE_KEY, divId: "adobe-dc-view" });

  adobeDCView.previewFile({
	content: { location: { url: window.location.origin + BASE_URL + VIEW_URL } },
    metaData: { fileName: VIEW_NAME, id: FILE_ID }
  }, {
    showAnnotationTools: true,
    enableAnnotationAPIs: true,
    includePDFAnnotations: true,
    embedMode: "FULL_WINDOW"
  }).then(adobeViewer => {
    adobeViewer.getAnnotationManager().then(annotManager => {
      globalAnnotManager = annotManager;

      // ===== Anti Guest (set user config lengkap) =====
      annotManager.setConfig({
        user: { id: CURRENT_USER.id, name: CURRENT_USER.name, type: "Person" },
        annotationConfig: { defaultAppearance: { strokeColor: stringToColor(CURRENT_USER.name) } }
      });

      // ===== Saat annotation dibuat: langsung paksa author/creator =====
      annotManager.registerEventListener(function(event) {
        if (event.type === "ANNOTATION_ADDED") {
          const ann = event.data;
          // Jangan panggil updateAnnotation untuk memaksa author/creator.
          // Di banyak versi Adobe SDK, field ini read-only dan akan memicu error
          // "No updated allowed field..." yang bikin sinkronisasi gagal.
          // User identity sudah ditentukan lewat setConfig(user).
        }
      }, { listenAll: true });

      function syncData() {
        fetch(`get_annotation.php?file_id=${encodeURIComponent(FILE_ID)}&t=${Date.now()}`)
          .then(r => r.ok ? r.json() : [])
          .then(data => {
            if (!Array.isArray(data)) return;
            const newAnnots = data.filter(a => a && a.id && !loadedIds.has(a.id));
            if (!newAnnots.length) return;

            annotManager.addAnnotations(newAnnots)
              .then(() => {
                newAnnots.forEach(a => loadedIds.add(a.id));
                const el = document.getElementById("sync-info");
                if (el) el.innerText = "Update: " + new Date().toLocaleTimeString();
              })
              .catch(err => {
                console.error('addAnnotations error', err);
                const el = document.getElementById("sync-info");
                if (el) el.innerText = "Sync gagal (lihat Console)";
              });
          })
          .catch(e => console.error("Sync error", e));
      }

      syncData();
      setInterval(syncData, 4000);
    });
  });
});

// ================ SIMPAN ANNOTATION ================
function saveToDatabase() {
  if (!globalAnnotManager) return;

  globalAnnotManager.getAnnotations().then(annotations => {
    if (!annotations.length) return alert("Tidak ada coretan.");

    // paksa author/creator (client-side)
    const finalizedAnnots = annotations.map(ann => {
      ann.author = CURRENT_USER.name;
      ann.creator = { id: CURRENT_USER.id, name: CURRENT_USER.name, type: "Person" };
      return ann;
    });

    fetch("save_annotation.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ file_id: FILE_ID, annotations: finalizedAnnots })
    })
    .then(async r => {
      const txt = await r.text();          // biar kalau PHP error, kelihatan isinya
      try { return JSON.parse(txt); }
      catch(e) { throw new Error("Response bukan JSON: " + txt.slice(0,200)); }
    })
    .then(res => {
      if (res.status === "success") {
        alert("✅ Tersimpan sebagai: " + CURRENT_USER.name);
        finalizedAnnots.forEach(a => loadedIds.add(a.id));

        // NOTE:
        // save_annotation.php akan menghapus file approve jika ada.
        // Jadi kalau sedang stamp view, kita balik ke mode work (tanpa stamp)
        if (IS_STAMP_VIEW) {
          window.location.href = "index.php?pdf_id=" + PDF_ID_NUM;
        }
      } else {
        alert("❌ Gagal: " + (res.message || "Unknown"));
      }
    })
    .catch(e => alert("❌ Error: " + e.message));
  });
}

function changeName() {
  let newName = prompt("Ganti Nama Anda:", CURRENT_USER.name);
  if (newName && newName !== CURRENT_USER.name) {
    window.location.href = "?pdf_id=<?= (int)$pdf_id ?>&name=" + encodeURIComponent(newName);
  }
}

<?php if ($canStamp): ?>
// ================ STAMP UI ================
let stampPdfDoc=null, stampCanvas=null, stampCtx=null, stampCurrentPage=1;

function openStampModal() {
  document.getElementById('stamp-modal').style.display = 'flex';
  if (!stampCanvas) {
    stampCanvas = document.getElementById('stamp-canvas');
    stampCtx = stampCanvas.getContext('2d');
    initStampInteract();
    loadStampPdf();
  } else {
    renderStampPreview();
  }
}

function closeStampModal(){ document.getElementById('stamp-modal').style.display='none'; }

async function loadStampPdf() {
  pdfjsLib.GlobalWorkerOptions.workerSrc = 'assets/vendor/pdfjs/pdf.worker.min.js?v=1';
  const url = window.location.origin + BASE_URL + WORK_URL;
  stampPdfDoc = await pdfjsLib.getDocument({ url }).promise;

  document.getElementById('stamp-page').max = stampPdfDoc.numPages;
  document.getElementById('stamp-status').innerText = `Preview OK. Total halaman: ${stampPdfDoc.numPages}`;
  await renderStampPreview();
}

async function renderStampPreview() {
  if (!stampPdfDoc) return;

  const p = parseInt(document.getElementById('stamp-page').value || '1', 10);
  stampCurrentPage = Math.min(Math.max(p, 1), stampPdfDoc.numPages);

  const page = await stampPdfDoc.getPage(stampCurrentPage);
  const wrap = document.getElementById('stamp-preview-wrap');
  const targetWidth = Math.min(760, wrap.clientWidth - 40);

  const viewport0 = page.getViewport({ scale: 1 });
  const scale = targetWidth / viewport0.width;
  const viewport = page.getViewport({ scale });

  stampCanvas.width = Math.floor(viewport.width);
  stampCanvas.height = Math.floor(viewport.height);

  stampCanvas.style.width = stampCanvas.width + "px";
  stampCanvas.style.height = stampCanvas.height + "px";

  stampCtx.clearRect(0, 0, stampCanvas.width, stampCanvas.height);
  await page.render({ canvasContext: stampCtx, viewport }).promise;

  const box = document.getElementById('stamp-box');
  const img = document.getElementById('stamp-img');
  const type = document.getElementById('stamp-type').value;

  img.src = `assets/stamps/${type}.png?v=${Date.now()}`;
  box.style.display = 'block';

  const defaultW = Math.max(140, Math.round(stampCanvas.width * 0.22));
  const defaultH = Math.max(60,  Math.round(stampCanvas.height * 0.08));

  const left = stampCanvas.width - defaultW - 20;
  const top  = stampCanvas.height - defaultH - 20;

  box.style.width  = `${defaultW}px`;
  box.style.height = `${defaultH}px`;
  box.style.transform = `translate(${left}px, ${top}px)`;
  box.setAttribute('data-x', left);
  box.setAttribute('data-y', top);
}

function resetStampBox(){ renderStampPreview(); }

function initStampInteract() {
  const box = document.getElementById('stamp-box');

  interact(box).unset();

  interact(box)
    .draggable({
      inertia: false,
      listeners: {
        move (event) {
          const target = event.target;
          let x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
          let y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

          target.style.transform = `translate(${x}px, ${y}px)`;
          target.setAttribute('data-x', x);
          target.setAttribute('data-y', y);
        }
      }
    })
    .resizable({
      edges: { left: false, right: true, bottom: true, top: false },
      listeners: {
        move (event) {
          const target = event.target;
          target.style.width  = event.rect.width + 'px';
          target.style.height = event.rect.height + 'px';
        }
      },
      modifiers: [ interact.modifiers.restrictSize({ min: { width: 60, height: 30 } }) ]
    });

  document.getElementById('stamp-type').addEventListener('change', () => {
    const type = document.getElementById('stamp-type').value;
    document.getElementById('stamp-img').src = `assets/stamps/${type}.png?v=${Date.now()}`;
  });
}

function getStampBoxPct() {
  const box = document.getElementById('stamp-box');
  const canvas = document.getElementById('stamp-canvas');

  const cw = canvas.width;
  const ch = canvas.height;

  const cssW = canvas.clientWidth || cw;
  const cssH = canvas.clientHeight || ch;

  const scaleX = cw / cssW;
  const scaleY = ch / cssH;

  const xCss = parseFloat(box.getAttribute('data-x')) || 0;
  const yCss = parseFloat(box.getAttribute('data-y')) || 0;

  const wCss = box.offsetWidth;
  const hCss = box.offsetHeight;

  const xCanvas = xCss * scaleX;
  const yCanvas = yCss * scaleY;
  const wCanvas = wCss * scaleX;
  const hCanvas = hCss * scaleY;

  return {
    x_pct: xCanvas / cw,
    y_pct_top: yCanvas / ch,
    w_pct: wCanvas / cw,
    h_pct: hCanvas / ch
  };
}

async function applyStampToPdf() {
  const status = document.getElementById('stamp-status');
  status.innerText = 'Memproses stamp...';

  const payload = {
    pdf_id: PDF_ID_NUM,
    page: stampCurrentPage,
    type: document.getElementById('stamp-type').value,
    ...getStampBoxPct()
  };

  try {
    const r = await fetch('stamp_apply.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const res = await r.json();
    if (!r.ok || res.status !== 'success') throw new Error(res.message || 'Gagal apply stamp');

    status.innerText = '✅ Berhasil.';
    window.location.href = "index.php?pdf_id=" + PDF_ID_NUM + "&stamp=1";
  } catch (e) {
    status.innerText = '❌ Error: ' + e.message;
  }
}
<?php endif; ?>
</script>
</body>
</html>
