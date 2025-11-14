<?php
// Jika kamu ingin memproses backend di PHP nanti, bisa ditaruh di sini.
// Untuk sekarang PHP hanya sebagai wrapper agar file bisa dijalankan di server.
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Skyline Helicopter — Cerdas QA</title>
  <style>
    :root{
      --bg:#03040a;
      --card:#090b12;
      --primary:#00e5ff;
      --accent:#00aaff;
      --muted:#7cc7ff;
      --glass: rgba(11,99,214,0.06);
      font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    }
    *{box-sizing:border-box}
    html,body{height:100%;margin:0;background:linear-gradient(180deg,var(--bg),#ffffff);color:#ffffff}
    .wrap{max-width:1100px;margin:28px auto;padding:20px}
    header{display:flex;gap:16px;align-items:center}
    .logo{width:68px;height:68px;border-radius:12px;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:18px;box-shadow:0 6px 18px rgba(11,99,214,0.12)}
    h1{font-size:20px;margin:0}
    p.lead{color:#d0e6ff;margin-top:6px;font-size:13px}

    .card{background:var(--card);border-radius:14px;padding:18px;box-shadow:0 6px 20px rgba(16,24,40,0.06);margin-top:18px}

    .grid{display:grid;grid-template-columns:1fr 360px;gap:18px}
    @media (max-width:880px){.grid{grid-template-columns:1fr} .rightpanel{order:2}}

    .search{display:flex;gap:8px}
    input[type="search"]{flex:1;padding:12px 14px;border-radius:10px;border:1px solid #e6eefb;background:transparent;font-size:14px}
    button.btn{background:var(--primary);color:white;padding:10px 14px;border-radius:10px;border:none;font-weight:600;cursor:pointer}
    button.ghost{background:transparent;border:1px solid var(--primary);color:var(--primary)}

    .results{margin-top:12px}
    .result{padding:12px;border-radius:10px;border:1px solid #f1f7ff;background:linear-gradient(180deg,#0a0d12,#0f141e);margin-bottom:10px}
    .score{float:right;color:var(--muted);font-size:12px}
    .meta{font-size:13px;color:var(--muted);margin-bottom:6px}

    .rightpanel .card{position:sticky;top:20px}
    .kbd{background:#f1f7ff;padding:6px 8px;border-radius:8px;font-weight:600;color:var(--primary);display:inline-block}

    .quick-buttons{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
    .quick-buttons button{padding:8px 10px;border-radius:8px;border:1px solid rgba(11,99,214,0.12);background:transparent;cursor:pointer}

    footer{margin-top:18px;color:var(--muted);font-size:13px}
    .answer{margin-top:8px;color:#0b1220}
    .small{font-size:12px;color:var(--muted)}
  </style>
</head>
<body>
  <div class="wrap">
    <header>
      <div class="logo">SK</div>
      <div>
        <h1>Skyline Helicopter — Sistem Q&amp;A Kerusakan</h1>
        <p class="lead">Website demo: preloaded dataset kerusakan helikopter Skyline. Cari gejala, komponen, penyebab, dan rekomendasi tindakan perawatan.</p>
      </div>
    </header>

    <div class="card">
      <div style="display:flex;gap:12px;align-items:center;justify-content:space-between;flex-wrap:wrap">
        <div style="flex:1;min-width:240px">
          <div class="search">
            <input id="query" type="search" placeholder="Tanya tentang kerusakan: contoh 'getaran berat di rotor' atau 'kebocoran oli gearbox'" />
            <button id="ask" class="btn">Cari</button>
            <button id="clear" class="ghost">Reset</button>
          </div>
          <div class="small" style="margin-top:10px">Tip: gunakan kata kunci singkat. Sistem melakukan pra-pemrosesan + TF-IDF + cosine similarity.</div>
        </div>

        <div style="width:260px">
          <div class="small">Model lokal (browser-only)</div>
          <div style="margin-top:8px"><span class="kbd">TF-IDF</span> + <span class="kbd">Cosine</span> • <span class="kbd">Preprocess (ID)</span></div>
        </div>
      </div>

      <div class="grid">
        <main>
          <div id="results" class="results" aria-live="polite"></div>
        </main>

        <aside class="rightpanel">
          <div class="card">
            <strong>Quick actions</strong>
            <div class="small">Contoh pertanyaan & navigasi</div>

            <div class="quick-buttons" style="margin-top:12px">
              <button data-q="getaran rotor saat idle">Getaran rotor saat idle</button>
              <button data-q="kebocoran oli gearbox">Kebocoran oli gearbox</button>
              <button data-q="power loss mesin setelah takeoff">Power loss mesin setelah takeoff</button>
              <button data-q="masalah ekor tidak stabil">Masalah ekor tidak stabil</button>
              <button data-q="indikator tekanan hidrolik turun">Tekanan hidrolik turun</button>
              <button data-q="bunyi berdentum di gearbox">Bunyi berdentum di gearbox</button>
              <button data-q="overheating engine">Engine overheating</button>
              <button data-q="connector avionics korosi">Avionics connector korosi</button>
            </div>

            <hr style="margin:12px 0;border:none;border-top:1px solid #eef6ff" />
            <strong>Dataset</strong>
            <div class="small">Preloaded: komponen, gejala, sebab, aksi perbaikan.</div>

            <div style="margin-top:12px">
              <button id="download-json" class="ghost">Unduh dataset (JSON)</button>
              <button id="add-sample" class="ghost" style="margin-left:6px">Tambah contoh entry</button>
            </div>
          </div>
        </aside>
      </div>

      <footer>
        Demo lokal — Semua pemrosesan dilakukan di browser. Tidak mengirim data ke server.
      </footer>
    </div>
  </div>

  <script>
    /* Semua JavaScript original tetap sama (TF-IDF, search engine, dsb) */
    /* ========== JS TETAP, tidak dikurangi apa pun ========== */

    const DATASET = [
      {id:1,title:'Getaran berat pada rotor utama',component:'Rotors / Main Rotor',tags:['vibration','rotor','imbalance'],text:'Pilot melaporkan getaran berat di fuselage saat rpm rotor meningkat. Getaran terasa paling kuat pada frekuensi tertentu.' ,cause:'Ketidakseimbangan massa pada blade; ketidaktepatan pitch, benturan asing, bearing rotor aus, hub longgar.',action:'Periksa kebersihan blade, lakukan dynamic balancing, cek bearing hub, inspeksi fastener dan batalkan penerbangan jika severe.'},
      {id:2,title:'Kebocoran oli gearbox transmisi',component:'Gearbox / Transmission',tags:['oil leak','gearbox'],text:'Ditemukan bekas oli di casing gearbox dan tetesan pada support frame. Tekanan oli normal tapi level turun setelah 10 jam terbang.' ,cause:'Seal aus, flange retak, fitting longgar, overpressure, overheating.',action:'Periksa seal, cek torque fitting, ganti seal, lakukan dye test atau pressure test pada gearbox.'},
      {id:3,title:'Kehilangan tenaga setelah takeoff',component:'Engine / Powerplant',tags:['power loss','engine'],text:'Mesin mengalami kehilangan tenaga mendadak setelah takeoff; indikator RPM turun dan suhu naik.' ,cause:'Fuel contamination, fuel pump failure, compressor stall, ignition timing, ECU fault.',action:'Periksa sistem bahan bakar, sample fuel, check fuel pump, periksa filter dan fuel control unit.'},
      {id:4,title:'Tail rotor tidak responsif',component:'Tail Rotor',tags:['tail','yaw'],text:'Yaw oscillation dan respons lambat saat hover.' ,cause:'Linkage aus, hydraulic leak, control cable stretch, tail gearbox issue.',action:'Periksa linkage, hydraulic pressure, tension kabel, actuator.'},
      {id:5,title:'Penurunan tekanan hidrolik',component:'Hydraulics',tags:['hydraulic','pressure'],text:'Kontrol terasa berat, tekanan hidrolik turun.' ,cause:'Hose bocor, pump rusak, kontaminasi, relief valve error.',action:'Inspeksi hose, cek reservoir, sample fluid, ganti filter.'},
      {id:6,title:'Bunyi dentum gearbox',component:'Gearbox',tags:['noise','gearbox'],text:'Dentuman berulang saat perubahan beban.' ,cause:'Kerusakan gigi, pitting, bearing rusak, misalignment.',action:'Stop operasi, lepaskan gearbox untuk inspeksi.'},
      {id:7,title:'Engine overheating',component:'Engine',tags:['overheat'],text:'Temperatur coolant/oil naik berlebih.' ,cause:'Aliran udara kurang, duct tersumbat, oil contamination.',action:'Periksa duct, radiator, oil cooler, thermostat.'},
      {id:8,title:'Konektor avionik korosi',component:'Avionics',tags:['avionics','corrosion'],text:'Display intermittent saat getaran.' ,cause:'Moisture ingress, sealing buruk.',action:'Bersihkan, impregnate connector, ganti pin rusak.'}
    ];

    const STOPWORDS_ID = new Set(["dan","atau","yang","di","ke","dari","dengan","pada","untuk","adalah","ini","itu","sebagai","telah","oleh","karena","jika","saat","beberapa","seperti"]);
    const STOPWORDS_EN = new Set(["the","and","or","is","in","on","of","a","an","to","for","with","by","that","this"]);

    function normalizeText(t){return t.toString().toLowerCase().replace(/[\u2018\u2019\u201c\u201d]/g,'"').replace(/[^\p{L}0-9\s]/gu,' ').replace(/\s+/g,' ').trim();}
    function tokenize(t){return normalizeText(t).split(' ').filter(Boolean);}
    function removeStopwords(tokens){return tokens.filter(w => !(STOPWORDS_ID.has(w) || STOPWORDS_EN.has(w)));}
    function stem(word){let w=word;w=w.replace(/(lah|kah|nya|ku|mu)$/,'');w=w.replace(/(kan|i|an)$/,'');w=w.replace(/^(me|mem|men|meng|pem|pen|peng|ber|per|ter)/,'');return w.length>2?w:word;}
    function preprocess(text){return removeStopwords(tokenize(text)).map(stem).filter(Boolean);}

    let VOCAB=[]; let IDF={}; let TFIDF_DOCS=[];

    function buildVectorSpace(dataset){
      const docsTokens = dataset.map(d => preprocess(d.title+' '+d.text+' '+(d.tags||[]).join(' ')));
      const vocabSet=new Set(); docsTokens.forEach(toks=>toks.forEach(t=>vocabSet.add(t))); VOCAB=[...vocabSet].sort();
      IDF={}; VOCAB.forEach(term => {let df=docsTokens.reduce((c,toks)=>c+(toks.includes(term)?1:0),0);IDF[term]=Math.log((docsTokens.length+1)/(df+1))+1;});
      TFIDF_DOCS = docsTokens.map(toks=>{const tf={}; toks.forEach(t=>tf[t]=(tf[t]||0)+1); const vec={}; const norm=Math.sqrt(Object.values(tf).reduce((s,c)=>s+c*c,0))||1; Object.keys(tf).forEach(t=>vec[t]=(tf[t]/norm)*IDF[t]); return vec;});
    }

    function tfidfVectorForTerms(terms){
      const tf={}; terms.forEach(t=>tf[t]=(tf[t]||0)+1); const vec={}; const norm=Math.sqrt(Object.values(tf).reduce((s,c)=>s+c*c,0))||1;
      Object.keys(tf).forEach(t=>{const idf=IDF[t]||Math.log((DATASET.length+1)/1)+1; vec[t]=(tf[t]/norm)*idf;});
      return vec;
    }

    function dotProd(a,b){let s=0; for(const k in a){if(b[k]) s+=a[k]*b[k];} return s;}
    function normVec(a){let s=0; for(const k in a)s+=a[k]*a[k]; return Math.sqrt(s)||1;}
    function cosineSim(a,b){return dotProd(a,b)/(normVec(a)*normVec(b));}

    function search(query,topN=5){
      if(!query.trim()) return [];
      const qvec=tfidfVectorForTerms(preprocess(query));
      const scored=TFIDF_DOCS.map((docvec,idx)=>({idx,score:cosineSim(qvec,docvec)})).filter(r=>r.score>0).sort((a,b)=>b.score-a.score).slice(0,topN);
      return scored.map(s=>({score:s.score,doc:DATASET[s.idx]}));
    }

    const resultsEl=document.getElementById('results');
    function escapeHtml(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
    function renderResults(items){
      resultsEl.innerHTML='';
      if(!items.length){resultsEl.innerHTML='<div class="small">Tidak ditemukan.</div>'; return;}
      items.forEach(it=>{
        const el=document.createElement('div'); el.className='result';
        el.innerHTML=`<div><strong>${escapeHtml(it.doc.title)}</strong> <span class="score">score: ${it.score.toFixed(3)}</span></div>
                      <div class="meta">Komponen: ${escapeHtml(it.doc.component)}</div>
                      <div class="answer">${escapeHtml(it.doc.text)}</div>
                      <div class="small" style="margin-top:8px">Penyebab: ${escapeHtml(it.doc.cause)}<br>Aksi: ${escapeHtml(it.doc.action)}</div>`;
        resultsEl.appendChild(el);
      });
    }

    buildVectorSpace(DATASET);

    document.getElementById('ask').addEventListener('click',()=>renderResults(search(document.getElementById('query').value,7)));
    document.getElementById('query').addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();document.getElementById('ask').click();}});
    document.getElementById('clear').addEventListener('click',()=>{document.getElementById('query').value='';resultsEl.innerHTML='';});

    document.querySelectorAll('.quick-buttons button').forEach(b=>b.addEventListener('click',e=>{
      document.getElementById('query').value=e.target.dataset.q;
      document.getElementById('ask').click();
    }));

    document.getElementById('download-json').addEventListener('click',()=>{
      const blob=new Blob([JSON.stringify(DATASET,null,2)],{type:'application/json'});
      const url=URL.createObjectURL(blob); const a=document.createElement('a'); a.href=url; a.download='skyline_dataset.json'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
    });

    document.getElementById('add-sample').addEventListener('click',()=>{
      const newEntry={id:DATASET.length+1,title:'Contoh masalah baru',component:'Unknown',tags:['new'],text:'Deskripsi ditambahkan.',cause:'-',action:'-'};
      DATASET.push(newEntry); buildVectorSpace(DATASET); alert('Entry baru ditambahkan.');
    });
  </script>
</body>
</html>
