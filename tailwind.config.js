/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./index.php",
    "./about.php",
    "./auth.php",
    "./cart.php",
    "./contact.php",
    "./forgot_password.php",
    "./order_detail.php",
    "./order_history.php",
    "./order_success.php",
    "./product_detail.php",
    "./products.php",
    "./reset_password.php",
    "./thanhtoan.php",
    "./user_info.php",
    "./wishlist.php",
    "./admin/**/*.php",
    "./includes/**/*.php",
  ],
  theme: {
    extend: {
      colors: {
        "primary": "#b6e633",
        "primary-dark": "#9acc2a",
        "background-light": "#f7f8f6",
        "text-light": "#161811",
        "card-light": "#ffffff",
        "border-light": "#e3e5dc",
        "muted-light": "#7e8863",
      },
      fontFamily: {
        "display": ["Be Vietnam Pro", "sans-serif"]
      },
    }
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
