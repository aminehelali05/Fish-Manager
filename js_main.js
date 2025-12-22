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

