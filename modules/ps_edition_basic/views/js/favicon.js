// https://stackoverflow.com/questions/260857/changing-website-favicon-dynamically

const favicon_path = window.ps_edition_basic_favicon || '/modules/ps_edition_basic/views/favicon.png';
document.querySelector("link[rel~='icon']").href = favicon_path;