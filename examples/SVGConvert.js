/**
 * @see https://stackoverflow.com/a/28226736
 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/canvas
 * @see https://developer.mozilla.org/en-US/docs/Web/API/HTMLCanvasElement/toDataURL
 *
 * @created      25.09.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

export default class SVGConvert{

	/**
	 * Converts the SVG image from the source element into a raster image with the given size and format
	 * and inserts the resulting data URI into the src attribute of the given destination image element.
	 *
	 * @param {SVGElement|HTMLImageElement} source
	 * @param {HTMLImageElement} destination
	 * @param {Number<int>} width
	 * @param {Number<int>} height
	 * @param {String} format
	 * @return {void}
	 */
	static toDataURI(source, destination, width, height, format){
		// format is whatever the fuck the specification allows:
		// @see https://html.spec.whatwg.org/multipage/canvas.html#dom-canvas-todataurl-dev
		format = (format || 'image/png');

		if(!destination instanceof HTMLImageElement){
			throw new Error('destination is not an instance of HTMLImageElement');
		}

		if(source instanceof HTMLImageElement){
			source = SVGConvert.base64Decode(source);
		}

		SVGConvert.toCanvas(source, width, height)
		          .then(canvas => destination.src = canvas.toDataURL(format))
		          .catch(error => console.log('(╯°□°）╯彡┻━┻ ', error));
	}

	/**
	 * Draws the given SVG source on a canvas of the given size and returns the canvas element
	 *
	 * @param {SVGElement} source
	 * @param {Number<int>} width
	 * @param {Number<int>} height
	 * @return {Promise<HTMLCanvasElement>}
	 */
	static toCanvas(source, width, height){
		return new Promise((resolve, reject) => {

			if(!source instanceof SVGElement){
				throw new Error('source is not an instance of SVGElement');
			}

			// the source SVG element needs to be visible
			source.style.display    = 'inline-block';
			source.style.visibility = 'visible';

			// add/fix the width/height of the SVG element
			// @see https://bugzilla.mozilla.org/show_bug.cgi?id=700533#c39
			source.width.baseVal.valueAsString  = width;
			source.height.baseVal.valueAsString = height;

			try{
				// stringify the SVG element and create an object URL for it
				let svgString  = new XMLSerializer().serializeToString(source);
				let svgBlob    = new Blob([svgString], {type: 'image/svg+xml;charset=utf-8'});
				let tempUrl    = URL.createObjectURL(svgBlob);
				// create a temporary image
				let tempImg    = new Image(width, height);
				// trigger the onLoad event with the temporary blob URL
				tempImg.src    = tempUrl;
				// process the conversion in the onLoad event
				tempImg.onload = function(){
					// create a canvas element to draw the SVG on
					let canvas    = document.createElement('canvas');
					canvas.width  = width;
					canvas.height = height;
					canvas.getContext('2d').drawImage(tempImg, 0, 0);
					// revoke the temporary blob
					URL.revokeObjectURL(tempUrl);
					// return the canvas element
					resolve(canvas);
				};
			}
			catch(error){
				reject(error.message);
			}

		});
	}

	/**
	 * Converts the given SVG base64 data URI into a DOM element
	 *
	 * @param {HTMLImageElement} image
	 * @return {SVGElement}
	 */
	static base64Decode(image){

		if(!image instanceof HTMLImageElement){
			throw new Error('image is not an instance of HTMLImageElement');
		}

		if(!image.src || !image.src.includes('base64,')){
			throw new Error('invalid image source');
		}

		return new DOMParser().parseFromString(atob(image.src.split(',')[1]), 'image/svg+xml').firstChild;
	}

}
