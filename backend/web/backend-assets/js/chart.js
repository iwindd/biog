$(document).ready(function() {

	//Bar Chart
	
	Morris.Bar({
		element: 'bar-charts',
		data: [
			{ y: 'ร้านอาหาร', a: 50, b: 45 },
			{ y: 'ธนาคาร', a: 20,  b: 15 },
			{ y: 'ร้านถ่ายเอกสาร', a: 30,  b: 25 },
			// { y: '2009', a: 75,  b: 65 },
			// { y: '2010', a: 50,  b: 40 },
			// { y: '2011', a: 75,  b: 65 }5
			{ y: 'ร้านขายของชำ', a: 20, b: 18 }
		],
		xkey: 'y',
		ykeys: ['a', 'b'],
		labels: ['จำนวนการจอง', 'ยืนยันการจอง'],
		lineColors: ['#00c5fb','#0253cc'],
		lineWidth: '3px',
		barColors: ['#00c5fb','#0253cc'],
		resize: true,
		redraw: true
	});

	Morris.Bar({
		element: 'bar-charts-duration',
		data: [
			{ d: '1', a: 15, b: 12 },
			{ d: '2', a: 20,  b: 15 },
			{ d: '3', a: 40,  b: 28 },
			{ d: '4', a: 50,  b: 30 },
			{ d: '5', a: 60,  b: 40 },
			{ d: '6', a: 85,  b: 65 },
			{ d: '7', a: 95, b: 80 },
			{ d: '8', a: 100, b: 85 },
			{ d: '9', a: 150, b: 140 },
			{ d: '10', a: 190, b: 160 },
		],
		xkey: 'y',
		ykeys: ['a', 'b'],
		labels: ['จำนวนการจอง', 'ยืนยันการจอง'],
		lineColors: ['#00c5fb','#0253cc'],
		lineWidth: '3px',
		barColors: ['#00c5fb','#0253cc'],
		resize: true,
		redraw: true,
		hoverCallback: function (index, options, content, row) {
			console.log(row);
			var month = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม'];
			var hover = "<div class='morris-hover-row-label'> เดือน"+month[row.d-1]+"</div>";
			hover = hover + '<div class="morris-hover-point" style="color: #00c5fb">จำนวนการจอง: '+row.a+'</div>';
			hover = hover + '<div class="morris-hover-point" style="color: #28a745">ยืนยันการจอง: '+row.b+'</div>';
			return hover;
		},
	});

	Morris.Bar({
		element: 'bar-charts-area',
		data: [
			{ y: '1 - 15 ตร.ม.', a: 20, b: 15 },
			{ y: '16 - 25 ตร.ม.', a: 50,  b: 45 },
			{ y: '26 - 30 ตร.ม.', a: 30,  b: 25 },
			{ y: '31 - 40 ตร.ม.', a: 60, b: 58 },
			{ y: '41 - 50 ตร.ม.', a: 15, b: 10 },
			{ y: '51 - 70 ตร.ม.', a: 20, b: 18 },
			{ y: '71 - 80 ตร.ม.', a: 25, b: 20 },
			{ y: '81 - 100 ตร.ม.', a: 8, b: 4 },
			{ y: '101 - 150 ตร.ม.', a: 6, b: 6 },
			{ y: '151 - 250 ตร.ม.', a: 7, b: 5 },
			{ y: '251 - 500 ตร.ม.', a: 5, b: 4 },
			{ y: '501 - 1000 ตร.ม.', a: 2, b: 2 },
			{ y: '1001 - 2200 ตร.ม.', a: 10, b: 8 },
			{ y: '2201 - 5000 ตร.ม.', a: 2, b: 2 },
			{ y: 'มากกว่า 5000 ตร.ม.', a: 0, b: 0 },
		],
		xkey: 'y',
		ykeys: ['a', 'b'],
		labels: ['จำนวนการจอง', 'ยืนยันการจอง'],
		lineColors: ['#00c5fb','#0253cc'],
		lineWidth: '3px',
		barColors: ['#00c5fb','#0253cc'],
		resize: true,
		redraw: true
	});

	Morris.Bar({
		element: 'bar-charts-tenant',
		data: [
			{ y: 'ส่วนราชการ', a: 20, b: 15 },
			{ y: 'รัฐวิสาหกิจ', a: 50,  b: 45 },
			{ y: 'องค์การมหาชน', a: 30,  b: 25 },
			{ y: 'ธนาคาร', a: 12, b: 10 },
			{ y: 'ประกันภัย', a: 15, b: 10 },
			{ y: 'กิจการร้านค้า', a: 20, b: 18 },
			{ y: 'กิจการรร้านอาหาร', a: 60, b: 55 },
			{ y: 'ร้านประจำ', a: 50, b: 50 },
			{ y: 'ร้านเครื่องดื่ม', a: 16, b: 15 },
		],
		xkey: 'y',
		ykeys: ['a', 'b'],
		labels: ['จำนวนการจอง', 'ยืนยันการจอง'],
		lineColors: ['#00c5fb','#0253cc'],
		lineWidth: '3px',
		barColors: ['#00c5fb','#0253cc'],
		resize: true,
		redraw: true
	});
	
	// Line Chart
	
	// Morris.Line({
	// 	element: 'line-charts',
	// 	data: [
	// 		{ d: '1', a: 15, b: 12 },
	// 		{ d: '2', a: 20,  b: 15 },
	// 		{ d: '3', a: 40,  b: 28 },
	// 		{ d: '4', a: 50,  b: 30 },
	// 		{ d: '5', a: 60,  b: 40 },
	// 		{ d: '6', a: 85,  b: 65 },
	// 		{ d: '7', a: 95, b: 80 },
	// 		{ d: '8', a: 100, b: 85 },
	// 		{ d: '9', a: 150, b: 140 },
	// 		{ d: '10', a: 190, b: 160 },
	// 	],
	// 	xkey: 'd',
		
	// 	ykeys: ['a', 'b'],
	// 	labels: ['จำนวนการจอง', 'ยืนยันการจอง'],
	// 	lineColors: ['#00c5fb','#28a745'],
	// 	lineWidth: '3px',
	// 	resize: true,
	// 	redraw: true,
	// 	hoverCallback: function (index, options, content, row) {
	// 		console.log(row);
	// 		var month = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม'];
	// 		var hover = "<div class='morris-hover-row-label'> เดือน"+month[row.d-1]+"</div>";
	// 		hover = hover + '<div class="morris-hover-point" style="color: #00c5fb">จำนวนการจอง: '+row.a+'</div>';
	// 		hover = hover + '<div class="morris-hover-point" style="color: #28a745">ยืนยันการจอง: '+row.b+'</div>';
	// 		return hover;
	// 	},

	// });
		
});