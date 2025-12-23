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
document.addEventListener('DOMContentLoaded', function(){
    // counters
    function animateCounter(id){
        const el = document.getElementById(id);
        if(!el) return;
        const value = parseInt(el.textContent) || 0;
        let start = 0;
        const duration = 800;
        const step = (timestamp)=>{
            start += Math.ceil(value / (duration / 16));
            if(start >= value) { el.textContent = value; return; }
            el.textContent = start;
            requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    }
    ['clientsCount','fishCount','salesCount'].forEach(animateCounter);

    // tilt cards
    document.querySelectorAll('.card').forEach(card=>{
        card.addEventListener('mousemove', function(e){
            const rect = card.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;
            card.style.transform = `translateY(-8px) rotateX(${(-y*8).toFixed(2)}deg) rotateY(${(x*8).toFixed(2)}deg)`;
        });
        card.addEventListener('mouseleave', ()=>{ card.style.transform = ''; });
    });

    // reveal on scroll
    const io = new IntersectionObserver((entries)=>{
        entries.forEach(e=>{ if(e.isIntersecting){ e.target.classList.add('visible'); io.unobserve(e.target); } });
    }, {threshold:0.12});
    document.querySelectorAll('.fade-in').forEach(el=> io.observe(el));

    // attach form validations if present
    const cf = document.getElementById('clientForm'); if(cf) cf.addEventListener('submit', (e)=>{ if(!validateClientForm()){ e.preventDefault(); } });
    const ff = document.getElementById('fishForm'); if(ff) ff.addEventListener('submit', (e)=>{ if(!validateFishForm()){ e.preventDefault(); } });
    const of = document.getElementById('orderForm'); if(of) of.addEventListener('submit', (e)=>{ /* basic check handled by server */ });

    // button ripple
    document.querySelectorAll('button').forEach(btn=>{
        btn.addEventListener('click', function(e){
            const r=document.createElement('span'); r.className='ripple';
            const size=Math.max(btn.offsetWidth, btn.offsetHeight); r.style.width=r.style.height=size+'px';
            r.style.left=(e.offsetX - size/2)+'px'; r.style.top=(e.offsetY - size/2)+'px';
            btn.appendChild(r); setTimeout(()=>r.remove(),700);
        });
    });
});

