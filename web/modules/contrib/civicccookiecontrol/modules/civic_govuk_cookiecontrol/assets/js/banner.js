const cookieBannerButtonAcceptHide = document.querySelector('.cookie-banner-accept--hide');
const cookieBannerButtonRejectHide = document.querySelector('.cookie-banner-reject--hide');
const cookieBannerButtonAccept = document.querySelector('.cookie-banner-button--accept');
const cookieBannerButtonReject = document.querySelector('.cookie-banner-button--reject');
const cookieBannerAccept = document.querySelector('.cookie-banner--accept');
const cookieBannerReject = document.querySelector('.cookie-banner--reject');
const cookieBannerMain = document.querySelector('.cookie-banner--main');
const cookieBanner = document.querySelector('.cookie-banner');
const body = document.getElementsByTagName('BODY')[0];

if (cookieBanner.classList.contains('fixed-top')
    && !body.classList.contains('toolbar-fixed')
) {
    var bannerHeight = cookieBanner.offsetHeight;
    body.style.paddingTop = bannerHeight + 'px';
}

function hideElement(element)
{
    element.classList.add('hidden');
    body.style.paddingTop = 0;
}

function showElement(element)
{
    element.setAttribute('tabindex', -1);
    element.classList.remove('hidden');
}

function hideConfirmation(e)
{
    e.preventDefault();
    hideElement(cookieBanner);
    body.style.paddingTop = 0;
}

function showConfirmation(e)
{
    const cookieValue = e.target.value;
    e.preventDefault();
    hideElement(cookieBannerMain);

    if (cookieValue === 'accept') {
        showElement(cookieBannerAccept);
        //Cookie Control accept all
        CookieControl.acceptAll();
    }
    if (cookieValue === 'reject') {
        showElement(cookieBannerReject);
        //Cookie Control reject all
        CookieControl.rejectAll();
    }

    if (cookieBanner.classList.contains('fixed-top')
        && !body.classList.contains('toolbar-fixed')
    ) {
        bannerHeight = cookieBanner.offsetHeight;
        body.style.paddingTop = bannerHeight + 'px';
    }
}

const getCookieValue = function (name) {
    return document.cookie.match("(^|;)\\s*" + name + "\\s*=\\s*([^;]+)") ? document.cookie.match("(^|;)\\s*" + name + "\\s*=\\s*([^;]+)").pop() : ""
};
if (document.cookie 
    && (getCookieValue("CookieControl").indexOf("accepted") !== -1 
    || getCookieValue("CookieControl").indexOf("revoked") !== -1)
) {
    hideElement(cookieBanner);
}

if (cookieBannerButtonAcceptHide != null) {
    cookieBannerButtonAcceptHide.addEventListener('click', hideConfirmation);
    cookieBannerButtonRejectHide.addEventListener('click', hideConfirmation);
}
if (cookieBanner != null) {
    cookieBannerButtonAccept.addEventListener('click', showConfirmation);
    cookieBannerButtonReject.addEventListener('click', showConfirmation);
}
