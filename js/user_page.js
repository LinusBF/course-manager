/**
 * Created by Linus on 2018-02-06.
 */
jQuery(document).ready(function($) {
	const COL_COUNT = 3;
	const containers = $('.cm_course');
	containers.each(function () {
		let container = $(this);
		let col_heights = [];
		for (let i = 0; i <= COL_COUNT; i++) {
			col_heights.push(0);
		}
		container.children().each(function(i) {
			const order = (i + 1) % COL_COUNT || COL_COUNT;
			$(this).css('order', order);
			const h = $(this).css('height');
			col_heights[order] += parseFloat(h);
		});
		const highest = Math.max.apply(Math, col_heights);
		container.css('height', highest + 'px');
	});
});
