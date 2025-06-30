// slider-servicios-secundarios.js
// Script para el slider de servicios secundarios

document.addEventListener("DOMContentLoaded", function () {
  const track = document.querySelector(".slider-servicios-track");
  const slides = document.querySelectorAll(".slide-servicio");
  const prevBtn = document.querySelector(".slider-servicios-prev");
  const nextBtn = document.querySelector(".slider-servicios-next");
  let currentIndex = 0;
  const totalSlides = slides.length;

  function updateSlider() {
    slides.forEach((slide, idx) => {
      slide.style.display = idx === currentIndex ? "block" : "none";
    });
  }

  prevBtn.addEventListener("click", function () {
    currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
    updateSlider();
  });

  nextBtn.addEventListener("click", function () {
    currentIndex = (currentIndex + 1) % totalSlides;
    updateSlider();
  });

  // Inicializar
  updateSlider();
});
