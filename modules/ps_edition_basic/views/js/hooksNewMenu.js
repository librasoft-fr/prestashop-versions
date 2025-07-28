function displayNavBar(){
	// Replace native nav-bar by webComponent simplify side-bar
	var wrapperSideBar = document.getElementById('wrapper_side_bar');

	if (wrapperSideBar) {
		var parentSideBar = wrapperSideBar.closest('.nav-bar');
		const classes = wrapperSideBar.classList;
		if (classes.contains('collapsed')) {
			parentSideBar.classList.add('collapsed');
		}
		parentSideBar.innerHTML = wrapperSideBar.innerHTML;
	}
}


function displayBreadcrumb(){
	// Replace native breadcrum by webComponent simplify breadcrumb
	var contentBreadcrumb = document.getElementById('content_breadcrumb');

	if (contentBreadcrumb) {
		// test new-theme
		var parentBreadcrumb = document.querySelector('.header-toolbar ol.breadcrumb');

		if (parentBreadcrumb) {
			parentBreadcrumb.innerHTML = contentBreadcrumb.innerHTML;
		}
		// legacy default
		else{
			parentBreadcrumb = document.querySelectorAll('ul.breadcrumb');

			parentBreadcrumb.forEach(
				(elem) => {
					elem.innerHTML = contentBreadcrumb.innerHTML;
				}
			)
		}

		document.getElementById('wrapper_breadcrumb').remove();
	}
}
