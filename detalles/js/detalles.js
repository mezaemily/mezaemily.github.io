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
    }

    // 🔹 --- NUEVAS FUNCIONALIDADES AÑADIDAS AQUÍ --- 🔹

    // Variables base
    let basePrice = 16.00;
    let selectedSize = "100ml";
    let quantity = 1;

    // Mostrar total debajo del precio
    const priceElement = document.querySelector(".price .current");
    const totalContainer = document.createElement("p");
    totalContainer.classList.add("total-display");
    document.querySelector(".price").after(totalContainer);

    const updateTotal = ()=>{
        let currentPrice = selectedSize === "50ml" ? 15 : basePrice;
        let subtotal = currentPrice * quantity;
        let discount = 0;

        if (quantity > 10) discount = 0.2;
        else if (quantity > 5) discount = 0.1;

        let total = subtotal - subtotal * discount;

        totalContainer.textContent = discount
          ? `Total: $${total.toFixed(2)} (con ${discount * 100}% de descuento)`
          : `Total: $${total.toFixed(2)}`;
    };

    // Recalcular precio al cambiar de tamaño
    let sizeBtns = document.querySelectorAll(".size-btn");
    sizeBtns.forEach(btn=>{
        btn.addEventListener("click",(evt)=>{
            selectedSize = evt.target.textContent.trim();
            priceElement.textContent = selectedSize === "50ml" ? "$15.00" : "$16.00";
            updateTotal();
        });
    });

    // --- Incrementar/Decrementar ---
    const btnInc = document.querySelector("#increase");
    const btnDec = document.querySelector("#decrease");
    const inputQty = document.querySelector("#quantity");

    btnInc.addEventListener("click",()=>{
        if(quantity < 15){
            quantity++;
            inputQty.value = quantity;
            updateTotal();
        }
    });

    btnDec.addEventListener("click",()=>{
        if(quantity > 1){
            quantity--;
            inputQty.value = quantity;
            updateTotal();
        }
    });

    inputQty.addEventListener("keydown",(e)=>{
        if(e.key === "Enter"){
            const val = parseInt(inputQty.value);
            if(!isNaN(val) && val >= 1 && val <= 15){
                quantity = val;
            }else{
                alert("Ingresa una cantidad válida entre 1 y 15");
                inputQty.value = quantity;
            }
            updateTotal();
        }
    });

    // --- Estrellas aleatorias ---
    const ratingDiv = document.querySelector(".rating");
    const randomStars = Math.random() * 4 + 1; // 1 a 5
    const starCount = Math.floor(randomStars);
    const halfStar = randomStars - starCount >= 0.5;

    ratingDiv.innerHTML = "";
    for (let i = 0; i < starCount; i++) {
        ratingDiv.innerHTML += '<i class="fas fa-star"></i>';
    }
    if (halfStar) ratingDiv.innerHTML += '<i class="fas fa-star-half-alt"></i>';
    ratingDiv.innerHTML += `<span>${(Math.random() * 300 + 50).toFixed(0)} reviews</span>`;

    updateTotal();

    // --- Comentarios ---
    const reviewsContainer = document.getElementById("reviews-container");

    // Formulario para comentarios
    const form = document.createElement("form");
    form.innerHTML = `
      <h3>Deja tu comentario</h3>
      <input type="text" id="name" placeholder="Tu nombre" required>
      <textarea id="comment" placeholder="Escribe tu comentario..." required></textarea>
      <button type="submit">Enviar</button>
    `;
    reviewsContainer.before(form);

    form.style.display = "flex";
    form.style.flexDirection = "column";
    form.style.gap = "10px";

    const renderReviews = ()=>{
        reviewsContainer.innerHTML = "";
        const saved = JSON.parse(localStorage.getItem("reviews") || "[]");
        saved.forEach(r=>{
            const div = document.createElement("div");
            div.classList.add("review");
            div.innerHTML = `
                <strong>${r.name}</strong>
                <p>${r.comment}</p>
            `;
            reviewsContainer.appendChild(div);
        });
    };

    form.addEventListener("submit",(e)=>{
        e.preventDefault();
        const name = form.querySelector("#name").value.trim();
        const comment = form.querySelector("#comment").value.trim();

        if(!name || !comment){
            alert("Completa todos los campos.");
            return;
        }

        const saved = JSON.parse(localStorage.getItem("reviews") || "[]");
        saved.push({name, comment});
        localStorage.setItem("reviews", JSON.stringify(saved));

        form.reset();
        renderReviews();
    });

    renderReviews();
}
