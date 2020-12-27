// reference: http://px-download.s3.amazonaws.com/SDK/ESCPOS_Command_Manual.pdf
ESCPrint = {

	constants: {
		ESC: 0x1b,
		FS: 0x1c,
		GS: 0x1d,
		LF: 0x0a,
	},

	_charToByte: function(char){

		switch (char){
			case '¢':
				return 0xa2;
			break;
			case '£':
				return 0xa3;
			break;
			case '€':
				return 0x80;
			break;
			case '¥':
				return 0xa5;
			break;
		}

		return String.prototype.charCodeAt.call(char);

	},

	_stringToBytes: function(encoder, str){
		str = [].slice.call(encoder.encode(str));
		return str;
	},

	alignmentStyle: function(style){

		let v = 0x00;

		if (style == 'center') {
			v = 0x01
		}

		if (style == 'right') {
			v = 0x02
		}

		return [ESCPrint.constants.ESC, 0x61, v];

	},

	characterStyle: function(style){

		let v = 0;

		if (style.smallFont) {
			v |= 1 << 0;
		}

		if (style.normalFont) {
			v |= 1 << 1;
		}

		if (style.normalFontAlternate) {
			v |= 1 << 2;
		}

		if (style.emphasized) {
			v |= 1 << 3;
		}

		if (style.doubleHeight) {
			v |= 1 << 4;
		}

		if (style.doubleWidth) {
			v |= 1 << 5;
		}

		if (style.six) {
			v |= 1 << 6;
		}

		if (style.underline) {
			v |= 1 << 7;
		}

		return [ESCPrint.constants.ESC, 0x21, v];

	},

	claimInterface: async function(device){

		if (device.opened === false){
			await device.open();
			if (device.configuration === null){
				await device.selectConfiguration(1);
			}
		}

		for (const config of device.configurations) {
	    	for (const iface of config.interfaces) {
				if (!iface.claimed) {
	        		await device.claimInterface(iface.interfaceNumber);
					return true;
	      		}
	    	}
	  	}

	  	return false;
	},

	getDevices: async function(){
		const devices = await navigator.usb.getDevices();

        var filteredDevices = [];
        for (var i = 0; i < devices.length; ++i){
            if (devices[i].usbVersionMajor > 0){
                filteredDevices.push(devices[i]);
            }
        }

		return filteredDevices;
	},

	requestPermission: function(){

		navigator.usb
		.requestDevice({
			filters: []
		})
		.then(async(device) => {
			await ESCPrint.claimInterface(device);
			alert('Added!');
		});

	},

	selectEndpoint: function(device, direction){

		const endpoint = device.configuration
		.interfaces[0]
		.alternate
		.endpoints.find(ep => ep.direction == direction);

		if (endpoint == null)
			throw new Error(`Endpoint ${direction} not found in device interface.`);

		return endpoint
	},

	sendText: async function(device, settings, str){

		var endpoint;

		// usb
		if (device.transferOut){
			await ESCPrint.claimInterface(device);
			endpoint = ESCPrint.selectEndpoint(device, 'out');
		}

		let receiptArray = [];

        // codepage
        if (settings.codepage === undefined || !settings.codepage){
            settings.codepage = 16;
        }

        if (settings.encoding === undefined || !settings.encoding){
            settings.encoding = 'windows-1252';
        }

		// reset buffer
		receiptArray.push(ESCPrint.constants.ESC, 0x40);

		// set codepage
		receiptArray.push(ESCPrint.constants.ESC, 0x74, settings.codepage);

		// encoder
		let encoder = new TextEncoder(settings.encoding, { NONSTANDARD_allowLegacyEncoding: true });

		// lines before
		for (i=0; i<settings.lines_before; i++){
			receiptArray.push(ESCPrint.constants.LF);
		}

		if (!settings.characters_per_line){
			settings.characters_per_line = 48;
		}

		// justify left
		receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('left'));

		// set default font style
		receiptArray = receiptArray.concat(ESCPrint.characterStyle({ normalFont: true }));

		str = str.split("\n");

		// have we found an alignment command?
		let foundAlignment = '';

		for (i=0; i<str.length; i++){

			var o = str[i].trim();

			// alignments
			if (o.indexOf('|>') == 0 || o.indexOf('<|') == 0 || o.indexOf('||') == 0){

				// right align
				if (o.indexOf('|>') == 0){
					foundAlignment = 'right';
				} else if (o.indexOf('||') == 0){
					foundAlignment = 'center';
				} else {
					foundAlignment = 'left';
				}

				receiptArray = receiptArray.concat(ESCPrint.alignmentStyle(foundAlignment));

				o = o.substr(2);
			}

			// h4/5/6
			if (o.indexOf('####') == 0 || o.indexOf('#####') == 0 || o.indexOf('#####') == 0){

				o = o.replace('###### ', '').replace('##### ', '').replace('#### ', '').replace('### ', '').trim();

				// justify center
				if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('center'));

				// text size
				receiptArray = receiptArray.concat(ESCPrint.textSize(1, 1));

				// output string
				receiptArray = receiptArray.concat(ESCPrint._stringToBytes(encoder, o));

				// line feed
				receiptArray.push(ESCPrint.constants.LF);

				// justify left
				if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('left'));

			// h3
			} else if (o.indexOf('###') == 0){

				// get string after #
				o = o.replace('###', '').trim();

				// text size
				receiptArray = receiptArray.concat(ESCPrint.textSize(1, 1));

				// emphasised mode on
				receiptArray.push(ESCPrint.constants.ESC, 0x45, 0x1);

				// justify center
				if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('center'));

				// output string
				receiptArray = receiptArray.concat(ESCPrint._stringToBytes(encoder, o));

				// emphasised mode off
				receiptArray.push(ESCPrint.constants.ESC, 0x45, 0x0);

				// line feed
				receiptArray.push(ESCPrint.constants.LF);

				// justify left
				if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('left'));


			// h2
			} else if (o.indexOf('##') == 0){

				// get string after #
				o = o.replace('##', '').trim();

				// text size
				receiptArray = receiptArray.concat(ESCPrint.textSize(1, 2));

				// emphasised mode on
				receiptArray.push(ESCPrint.constants.ESC, 0x45, 0x1);

				// justify center
				if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('center'));

				// output string
				receiptArray = receiptArray.concat(ESCPrint._stringToBytes(encoder, o));

				// text size
				receiptArray = receiptArray.concat(ESCPrint.textSize(1, 1));

				// emphasised mode off
				receiptArray.push(ESCPrint.constants.ESC, 0x45, 0x0);

				// line feed
				receiptArray.push(ESCPrint.constants.LF);

				// justify left
				if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('left'));

			// h1
			} else if (o.indexOf('#') == 0){

				// get string after #
				o = o.replace('#', '').trim();

				// change text size
				receiptArray = receiptArray.concat(ESCPrint.textSize(1, 3));

				// emphasised mode on
				receiptArray.push(ESCPrint.constants.ESC, 0x45, 0x1);

				// justify center
				if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('center'));

				// add string
				receiptArray = receiptArray.concat(ESCPrint._stringToBytes(encoder, o));

				// reset text size
				receiptArray = receiptArray.concat(ESCPrint.textSize(1, 1));

				// emphasised mode off
				receiptArray.push(ESCPrint.constants.ESC, 0x45, 0x0);

				// line feed
				receiptArray.push(ESCPrint.constants.LF);

				// justify left
				if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('left'));

			// hr
			} else if (o.indexOf('*****') === 0 || o.indexOf('-----') === 0){

				receiptArray.push(ESCPrint.constants.LF);

				// justify center
				//if (foundAlignment == '')
				receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('center'));

				// add lines
				receiptArray = receiptArray.concat(ESCPrint._stringToBytes(encoder, '_'.repeat(settings.characters_per_line)));

				// line feed
				receiptArray.push(ESCPrint.constants.LF);

				// line feed
				receiptArray.push(ESCPrint.constants.LF);

				// justify left
				//if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('left'));
				if (foundAlignment != '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle(foundAlignment));

			// cut
			} else if (o.indexOf('>>>>>') === 0){

				receiptArray.push(ESCPrint.constants.GS);
				receiptArray.push(0x56); // cut
				receiptArray.push(0x42); // command 66 (feed to cut)
				receiptArray.push(0x0); // no extra feed

			// image keycode
			} else if (o.indexOf('[img') === 0){

				o = o.replace('[img', '').replace(']', '').trim().split(',');

				if (o.length == 2){

					if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('center'));
					receiptArray.push(0x1D, 0x28, 0x4C, 0x06, 0x00, 0x30, 0x45, o[0].trim().toString(16), o[1].trim().toString(16), 0x01, 0x01);
					if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('left'));

				}

			// QRcode
			} else if (o.indexOf('[qrcode') === 0){

				o = o.replace('[qrcode', '').replace(']', '').trim().split(',');

				if (o.length == 2){

					if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('center'));

					let data = ESCPrint._stringToBytes(encoder, o[1]);
					let storeLen = data.length + 3;
					let pL = storeLen % 256;
					let pH = storeLen / 256;

					receiptArray.push(ESCPrint.constants.GS, 0x28, 0x6b, 0x04, 0x00, 0x31, 0x41, 0x33, 0x00); // https://www.epson-biz.com/modules/ref_escpos/index.php?content_id=140
					receiptArray.push(ESCPrint.constants.GS, 0x28, 0x6b, 0x03, 0x00, 0x31, 0x43, o[0].toString(16)); // https://www.epson-biz.com/modules/ref_escpos/index.php?content_id=141
					receiptArray.push(ESCPrint.constants.GS, 0x28, 0x6b, 0x03, 0x00, 0x31, 0x45, 0x30); // https://www.epson-biz.com/modules/ref_escpos/index.php?content_id=142
					receiptArray.push(ESCPrint.constants.GS, 0x28, 0x6b, pL, pH, 0x31, 0x50, 0x30);  // https://www.epson-biz.com/modules/ref_escpos/index.php?content_id=143
					receiptArray = receiptArray.concat(data);
					receiptArray.push(ESCPrint.constants.GS, 0x28, 0x6b, 0x03, 0x00, 0x31, 0x51, 0x30); // https://www.epson-biz.com/modules/ref_escpos/index.php?content_id=144
					if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('left'));

				}

			// new line
			} else if (o.trim() == ''){

				// line feed
				receiptArray.push(ESCPrint.constants.LF);

			// standard text
			} else {

				// add string
				receiptArray = receiptArray.concat(ESCPrint._stringToBytes(encoder, o));

				// line feed
				receiptArray.push(ESCPrint.constants.LF);

			}

		};

		// lines after
		for (i=0; i<settings.lines_after; i++){
			receiptArray.push(ESCPrint.constants.LF);
		}

		// if autocut
		if (settings.autocut){

			receiptArray.push(ESCPrint.constants.GS);
			receiptArray.push(0x56); // cut
			receiptArray.push(0x42); // command 66 (feed to cut)
			receiptArray.push(0x0); // no extra feed

		}

		// #of copies
		for (i=0; i<settings.copies; i++){

			const bytes = new Uint8Array(receiptArray);

			// usb
			if (device.transferOut){
				device.transferOut(endpoint.endpointNumber, bytes);

			// ip
			} else {
				device.send(bytes);
			}

		}

		return true;

	},

	textSize: function(width, height){
		var c = (2 << 3) * (width - 1) + (height - 1);
		return [ESCPrint.constants.GS, 0x21, c];
	}

};
