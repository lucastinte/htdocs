const sliderTrack = document.querySelector(".slider-track");
const prevButton = document.querySelector(".slider-prev");
const nextButton = document.querySelector(".slider-next");

if (sliderTrack && prevButton && nextButton) {
  let currentSlide = 0;
  const totalSlides = Math.ceil(sliderTrack.children.length / 3);

  prevButton.addEventListener("click", () => {
    currentSlide = Math.max(currentSlide - 1, 0);
    sliderTrack.style.transform = `translateX(-${currentSlide * 100}%)`;
  });

  nextButton.addEventListener("click", () => {
    currentSlide = Math.min(currentSlide + 1, totalSlides - 1);
    sliderTrack.style.transform = `translateX(-${currentSlide * 100}%)`;
  });
}

// Slider infinito automático para .slider-track-infinite
const sliderTrackInfinite = document.querySelector(".slider-track-infinite");
if (sliderTrackInfinite) {
  let animationId;
  let pos = 0;
  const speed = 0.5; // menor = más lento
  function animate() {
    pos -= speed;
    if (Math.abs(pos) >= sliderTrackInfinite.scrollWidth / 2) {
      pos = 0;
    }
    sliderTrackInfinite.style.transform = `translateX(${pos}px)`;
    animationId = requestAnimationFrame(animate);
  }
  animate();
}
