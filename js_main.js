function validateClientForm(){
    const f=document.getElementById('clientForm');
    if(!f.nom.value.trim() || !f.prenom.value.trim()){ alert("Nom et prénom requis"); return false;}
    if(!f.telephone.value.trim() || f.telephone.value.length<8){ alert("Téléphone invalide"); return false;}
    return true;
}

function validateFishForm(){
    const f=document.getElementById('fishForm');
    if(!f.nom_fish.value.trim()){alert("Nom du poisson requis");return false;}
    if(f.quantite_kg.value<=0){alert("Quantité invalide");return false;}
    if(f.prix_achat.value<=0 || f.prix_vente.value<=0){alert("Prix invalide");return false;}
    return true;
}

function validateOrderForm(){
    const f=document.getElementById('orderForm');
    if(!f.id_client.value || !f.id_fish.value){alert("Client et Poisson requis");return false;}
    if(f.quantite.value<=0){alert("Quantité invalide");return false;}
    if(f.type_paiement.value=="Acompte" && f.montant_acompte.value<=0){alert("Montant acompte invalide");return false;}
    return true;
}
function addItem(){
  let div=document.createElement("div");
  div.innerHTML=`
  <select name="fish_id[]"></select>
  <input type="number" step="0.01" name="quantite[]">
  `;
  document.getElementById("items").appendChild(div);
}

// UI Animations
(function(){
    'use strict';

    function onReady(fn){
        if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn);
    }

    function animateCounter(id, duration = 800){
        const el = document.getElementById(id);
        if(!el) return;
        const value = parseInt(el.textContent) || 0;
        let start = 0;
        const startTime = performance.now();

        function step(now){
            const progress = Math.min((now - startTime) / duration, 1);
            el.textContent = Math.floor(progress * value);
            if(progress < 1) requestAnimationFrame(step); else el.textContent = value;
        }
        requestAnimationFrame(step);
    }

    function setupCounters(){
        ['clientsCount','fishCount','salesCount'].forEach(id=> animateCounter(id));
    }

    function setupRevealOnScroll(){
        const io = new IntersectionObserver((entries, obs)=>{
            entries.forEach(entry => {
                if(entry.isIntersecting){
                    entry.target.classList.add('visible');
                    obs.unobserve(entry.target);
                }
            });
        }, {threshold: 0.12});

        // Observe both explicit fade-in helpers and panels for a unified reveal
        document.querySelectorAll('.fade-in, .panel').forEach(el => io.observe(el));
    }

    function setupFormValidation(){
        const cf = document.getElementById('clientForm'); if(cf) cf.addEventListener('submit', (e)=>{ if(!validateClientForm()){ e.preventDefault(); } });
        const ff = document.getElementById('fishForm'); if(ff) ff.addEventListener('submit', (e)=>{ if(!validateFishForm()){ e.preventDefault(); } });
        const of = document.getElementById('orderForm'); if(of) of.addEventListener('submit', (e)=>{ /* server will validate */ });
    }

    function setupRipples(){
        document.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', function(e){
                const rect = btn.getBoundingClientRect();
                const r = document.createElement('span');
                r.className = 'ripple';
                const size = Math.max(rect.width, rect.height) * 1.2;
                r.style.width = r.style.height = size + 'px';
                const x = e.clientX - rect.left - size/2;
                const y = e.clientY - rect.top - size/2;
                r.style.left = x + 'px'; r.style.top = y + 'px';
                btn.appendChild(r);
                setTimeout(()=> r.remove(), 600);
            });
        });
    }

    onReady(function(){
        // page load visual
        setTimeout(()=> document.body.classList.add('is-loaded'), 40);

        setupCounters();
        setupRevealOnScroll();
        setupFormValidation();
        setupRipples();
    });

})();

