var pastaVertical = [
		// globals -----------------
		[	Xmenu.prototype.VERTICAL,
			1, // delay in sec. before closing menu
			true, // onclick / onmouseover
			true, // horizontal & vertical menu: menu appears below/right of the root-node
			false, // horizontal menu: each hierarchy starts on same X
			true, // keep expansion state
			true,	// highlight clicked nodes
			[ 'img/spacer.gif', 20, 1 ]	// XlayerParent img
		],
			// styles ------------------
			[	[ "#ff4400", "#ffaaaa" ], // onclick-menu: color of clicked node
				[ 20, 20, 100, 35, null, [ "#ff5500", "white", "center", true, "Arial, Helvetica", 14, false, "img/arrow_horiz.gif", 3, 5, 10 ],[ "#ff7c00", "#ffbbaa", "center", true, "Helvetica, Arial", 2, null, 1, 20, 0 ] ], // xlayer style: [xOffset, yOffset, width, height, fading: [start_val, stop_val, steps, delay(ms)], style onmouseout: [bgcolor, fgcolor, align, bold, fontFace, fontSize, img, img_width, img_height, tile(horiz/vert)], onmouseover: [ bgcolor, fgcolor, align, fontFace, fontSize ]]
				[ 0, 2, 100, 30, null, [ "#ff6a00", "white", "center", true, "Arial, Helvetica", 12, false, "img/arrow_horiz.gif", 3, 5, 10 ],[ "#ff7c00", "#ffbbaa", "center", true, "Helvetica, Arial", 1, "img/spacer.gif", 1, 1, 0 ] ],
				[ -8, 4, 100, 20, null, [ "#ff8e00", "white", "center", true, "Arial, Helvetica", 10, false, "img/arrow_horiz.gif", 3, 5, 10 ],[ "#ffa000", "#ffbbaa", "center", true, "Helvetica, Arial", 1, "img/spacer.gif", 1, 1, 0 ] ],
				[ -4, 4, 100, 20, null, [ "#ffb200", "white", "center", true, "Arial, Helvetica", 10, false, "img/arrow_horiz.gif", 3, 5, 10 ],[ "#ffc400", "#ffccbb", "center", true, "Helvetica, Arial", 1, "img/spacer.gif", 1, 1, 0 ] ]
			],
			// content ----------
			[	[ "pasta", null, 0 ], // content: [text, href, hierarchyLevel]
					[ "spaghetti", null, 1 ],
						[ "bolognese", null, 2 ],
							[ "formaggio", "#", 3 ],
						[ "carbonara", "#", 2 ],
						[ "pesto", null, 2 ],
							[ "rosso", "#", 3 ],
					[ "tortelloni", "#", 1 ],
					[ "tagliatelle", null, 1 ],
						[ "alla panna", null, 2 ],
						[ "arrabiato", "#", 2 ],
							[ "formaggio", "#", 3 ]
			]
		];
