
document.addEventListener('readystatechange', function(event) {
    if (document.readyState === "complete") {
        const minimizedGuide = document.querySelector("#setup-guide-minimized-container");
        if (minimizedGuide) {
		    minimizedGuide.style.display = 'none';
        }
    }
});
