const cookieCategories = document.getElementsByClassName('govuk-cookiecontrol-radios');
const cookieCategoryForms = document.getElementsByClassName('cookie-category-form');
const cookieCategoryMessage = document.getElementsByClassName('govuk-cookiecontrol-confirmation');

setTimeout(
    function () {
        if (cookieCategories) {
            for (var i = 0; i < cookieCategories.length; i++) {
                var categoryState = CookieControl.getCategoryConsent(i);

                if (categoryState === true) {
                    document.getElementById('accept-' + i).checked = true;
                } else if (categoryState === false) {
                    document.getElementById('reject-' + i).checked = true;
                }
            }
        }
    }, 1000
);

if (cookieCategoryForms) {
    for (var i = 0; i < cookieCategoryForms.length; i++) {
        cookieCategoryForms[i].addEventListener(
            "submit", function (e) {
                e.preventDefault();
                var categoryIndex = parseInt(this.dataset.index);

                if (document.getElementById('accept-' + categoryIndex).checked === true) {
                    CookieControl.changeCategory(categoryIndex,true);
                } else if (document.getElementById('reject-' + categoryIndex).checked === true) {
                    CookieControl.changeCategory(categoryIndex,false);
                }
                cookieCategoryMessage[categoryIndex].classList.remove('govuk-panel--hidden');
            }
        );
    }
}
