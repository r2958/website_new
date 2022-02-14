function toggle(id) {
	var obj = document.getElementById(id);
	if (obj.className == 'active') {
		obj.className = 'inactive';
	} else {
		obj.className = 'active';
	}
}

function tog(id) {
	toggle(id);
}