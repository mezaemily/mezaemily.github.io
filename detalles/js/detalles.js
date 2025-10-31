window.onload = ()=>{
    let imgGaleria = document.querySelector("#main-product-img")
    let imgs = document.querySelectorAll(".thumb")

    for(let i=0;i<imgs.length;i++){
        imgs[i].addEventListener('click',(evt)=>{
            console.log(evt.target)
            imgGaleria.src=evt.target.src.replace("thumbs/","")
           
            imgs.forEach(item=>{
                item.classList.remove('active')
            })
             evt.target.classList.add('active')
        })

            // --- Opciones de tamaño ---
    let sizeBtns = document.querySelectorAll(".size-btn");

    sizeBtns.forEach(btn => {
        btn.addEventListener('click', (evt)=>{
            // Quitar clase activa de todos los btn
            sizeBtns.forEach(b => b.classList.remove('active'));
            // Agg clase activa
            evt.target.classList.add('active');
            console.log("Tamaño seleccionado:", evt.target.textContent);
        });
    });
 

     peice = 34
    }
}//llave on load