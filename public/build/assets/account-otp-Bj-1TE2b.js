document.addEventListener("DOMContentLoaded",()=>{const e=document.querySelector("[data-otp-input]");e&&e.addEventListener("input",t=>{t.target.value=t.target.value.replace(/\D/g,"").slice(0,6)})});
