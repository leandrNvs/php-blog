/**
 * ==========================================================================
 *  MENU MOBILE
 * ==========================================================================
 */
const navbar = document.querySelector(".navbar");
const openNavbar = document.querySelector("button[aria-label='open menu']");
const closeNavbar = document.querySelector("button[aria-label='close menu']");

openNavbar.addEventListener("click", function () {
  navbar.classList.add("active");
});

closeNavbar.addEventListener("click", function () {
  navbar.classList.remove("active");
});

/**
 * ==========================================================================
 *  TOAST
 * ==========================================================================
 */
const toast = document.querySelector(".toast");

window.addEventListener("load", function () {
  const toastClass = toast.getAttribute("class").split(" ")[1];

  setTimeout(() => {
    toast.classList.replace(toastClass, "toast--none");
  }, 5000);
});
