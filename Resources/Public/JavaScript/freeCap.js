/***************************************************************
*  Copyright notice
*
*  (c) 2007-2023 Stanislas Rolland <typo3AAAA(arobas)sjbr.ca>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/*
 * Javascript functions for TYPO3 extension freeCap CAPTCHA (sr_freecap)
 *
 */
(function () {
	SrFreecap = {

		/*
		 * Loads a new freeCap image
		 *
		 * @param string id: identifier used to uniiquely identify the image
		 * @param string noImageMessage: message to be displayed if the image element cannot be found
		 * @return void
		 */
		newImage: function (id) {
			// extract image name from image source (i.e. cut off ?randomness)
			var theImage = document.getElementById('tx_srfreecap_captcha_image_' + id);
			var parts = theImage.src.split('&freecapSet');
			theImage.src = parts[0] + '&freecapSet=' + Math.round(Math.random()*100000);
		},

		imageLinkOnClick: function(event) {
			event.preventDefault();
			var links = document.querySelectorAll('a[data-srfreecap-image]');
			var link = links[0];
			var fakeId = link.getAttribute('data-srfreecap-image');
			link.blur();
			SrFreecap.newImage(fakeId);
			return false;
		},
		
		/*
		 * Plays the audio captcha
		 *
		 * @param string id: identifier used to uniquely identify the wav file
		 * @param string wavUrl: url of the wave file generating script
		 * @param string noPlayMessage: message to be displayed if the audio file cannot be rendered
		 * @return void
		 */
		playCaptcha: function (id, wavUrl, noPlayMessage) {
			if (document.getElementById) {
				var theAudio = document.getElementById('tx_srfreecap_captcha_playAudio_' + id);
				var url = wavUrl + '&freecapSet=' + Math.round(Math.random()*100000);
				while (theAudio.firstChild) {
					theAudio.removeChild(theAudio.firstChild);
				}
				var audioElement = document.createElement('audio');
				if (audioElement.canPlayType) {
					// HTML 5 audio
					if (audioElement.canPlayType('audio/mpeg') === 'maybe' || audioElement.canPlayType('audio/mpeg') === 'probably') {
						url = url.replace('formatName=wav', 'formatName=mp3');
					}
					audioElement.setAttribute('src', url);
					audioElement.setAttribute('id', 'tx_srfreecap_captcha_playAudio_audio' + id);
					theAudio.appendChild(audioElement);
					audioElement.load();
					audioElement.play();
				} else {
					url = url.replace('formatName=wav', 'formatName=mp3');
					// In IE, use the default player for audio/mpeg, probably Windows Media Player
					var objectElement = document.createElement('object');
					objectElement.setAttribute('id', 'tx_srfreecap_captcha_playAudio_object' + id);
					objectElement.setAttribute('type', 'audio/mpeg');
					theAudio.appendChild(objectElement);
					objectElement.style.height = 0;
					objectElement.style.width = 0;
					var parameters = {
						autoplay: 'true',
						autostart: 'true',
						controller: 'false',
						showcontrols: 'false'
					};
					for (var parameter in parameters) {
						if (parameters.hasOwnProperty(parameter)) {
							var paramElement = document.createElement('param');
							paramElement.setAttribute('name', parameter);
							paramElement.setAttribute('value', parameters[parameter]);
							paramElement = objectElement.appendChild(paramElement);
						}
					}
					objectElement.setAttribute('altHtml', '<a style="display:inline-block; margin-left: 5px; width: 200px;" href="' + url + '">' + (noPlayMessage ? noPlayMessage : 'Sorry, we cannot play the word of the image.') + '</a>');
				}
			} else {
				alert(noPlayMessage ? noPlayMessage : 'Sorry, we cannot play the word of the image.');
			}
		},

        audioLinkOnClick: function(event) {
	        event.preventDefault();
	        var links = document.querySelectorAll('a[data-srfreecap-audio],input[data-srfreecap-audio]');
	        var link = links[0];
			var fakeId = link.getAttribute('data-srfreecap-audio');
			link.blur();
			var audioUrl = link.getAttribute('data-srfreecap-audio-url');
			var noPlayMessage = link.getAttribute('data-srfreecap-audio-noplay');
			SrFreecap.playCaptcha(fakeId, audioUrl, noPlayMessage);
			return false;
		}
	};
	document.addEventListener('DOMContentLoaded', (event) => {
		document.body.addEventListener('click', function(event) {
		    if (event.target.closest('a[data-srfreecap-image]')) {
		        event.stopImmediatePropagation();
		        SrFreecap.imageLinkOnClick(event);
		    }
		    if (event.target.closest('a[data-srfreecap-audio],input[data-srfreecap-audio]')) {
		        event.stopImmediatePropagation();
		        SrFreecap.audioLinkOnClick(event);
		    }
		});
	});
})();