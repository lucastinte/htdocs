const sliderTrack = document.querySelector(".slider-track");
const prevButton = document.querySelector(".slider-prev");
const nextButton = document.querySelector(".slider-next");

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
