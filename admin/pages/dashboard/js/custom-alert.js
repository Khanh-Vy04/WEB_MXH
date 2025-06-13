// Tạo element cho custom alert
const customAlert = document.createElement("div");
customAlert.className = "custom-alert";
document.body.appendChild(customAlert);

// Override hàm alert mặc định
window.originalAlert = window.alert;
window.alert = function (message) {
  customAlert.textContent = message;
  customAlert.classList.add("show");

  // Tự động ẩn sau 3 giây
  setTimeout(() => {
    customAlert.classList.add("hide");
    setTimeout(() => {
      customAlert.classList.remove("show", "hide");
    }, 300);
  }, 3000);
};

// Hàm hiển thị alert với thời gian tùy chỉnh
function showCustomAlert(message, duration = 3000) {
  customAlert.textContent = message;
  customAlert.classList.add("show");

  setTimeout(() => {
    customAlert.classList.add("hide");
    setTimeout(() => {
      customAlert.classList.remove("show", "hide");
    }, 300);
  }, duration);
}

// Hàm hiển thị alert với callback
function showCustomAlertWithCallback(message, callback, duration = 3000) {
  customAlert.textContent = message;
  customAlert.classList.add("show");

  setTimeout(() => {
    customAlert.classList.add("hide");
    setTimeout(() => {
      customAlert.classList.remove("show", "hide");
      if (typeof callback === "function") {
        callback();
      }
    }, 300);
  }, duration);
}
