<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Three.JS Telus</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
		<style>
			body {
				color: #cccccc;
				font-family:Monospace;
				font-size:13px;
				text-align:center;

				background-color: #050505;
				margin: 0px;
				overflow: hidden;
			}

			#info {
				position: absolute;
				top: 0px; width: 100%;
				padding: 5px;
			}

			a {

				color: #0080ff;
			}


		</style>
	</head>
	<body>

		<div id="container"></div>
		<div id="info">ThreeJS Telus</div>
		<canvas id="render-canvas"></canvas>

		<script src="js/three.min.js"></script>
		<script src="js/effects/oculus.js"></script>
		<script src="js/effects/TrackballControls.js"></script>
		<script src="js/effects/PointerLockControls.js"></script>
		<script src="js/effects/FirstPersonControls.js"></script>
		<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
		<script src="js/VRRenderer.js"></script>


		<script src="js/Detector.js"></script>
		<script src="js/libs/stats.min.js"></script>

		<script>
			var camera, scene, renderer;
			var controls;
			var mesh;

			var clock = new THREE.Clock();
			var raycaster;

			var container, stats;
			var blocker = document.getElementById( 'blocker' );
			var instructions = document.getElementById( 'instructions' );

			var vrrenderer;
			var renderCanvas;

			var havePointerLock = 'pointerLockElement' in document || 'mozPointerLockElement' in document || 'webkitPointerLockElement' in document;

			/*if ( havePointerLock ) {

				var element = document.body;

				var pointerlockchange = function ( event ) {

					if ( document.pointerLockElement === element || document.mozPointerLockElement === element || document.webkitPointerLockElement === element ) {

						controls.enabled = true;

						blocker.style.display = 'none';

					} else {

						controls.enabled = false;

						blocker.style.display = '-webkit-box';
						blocker.style.display = '-moz-box';
						blocker.style.display = 'box';

						instructions.style.display = '';

					}

				}

				var pointerlockerror = function ( event ) {

					instructions.style.display = '';

				}

				// Hook pointer lock state change events
				document.addEventListener( 'pointerlockchange', pointerlockchange, false );
				document.addEventListener( 'mozpointerlockchange', pointerlockchange, false );
				document.addEventListener( 'webkitpointerlockchange', pointerlockchange, false );

				document.addEventListener( 'pointerlockerror', pointerlockerror, false );
				document.addEventListener( 'mozpointerlockerror', pointerlockerror, false );
				document.addEventListener( 'webkitpointerlockerror', pointerlockerror, false );

				instructions.addEventListener( 'click', function ( event ) {

					instructions.style.display = 'none';

					// Ask the browser to lock the pointer
					element.requestPointerLock = element.requestPointerLock || element.mozRequestPointerLock || element.webkitRequestPointerLock;

					if ( /Firefox/i.test( navigator.userAgent ) ) {

						var fullscreenchange = function ( event ) {

							if ( document.fullscreenElement === element || document.mozFullscreenElement === element || document.mozFullScreenElement === element ) {

								document.removeEventListener( 'fullscreenchange', fullscreenchange );
								document.removeEventListener( 'mozfullscreenchange', fullscreenchange );

								element.requestPointerLock();
							}

						}

						document.addEventListener( 'fullscreenchange', fullscreenchange, false );
						document.addEventListener( 'mozfullscreenchange', fullscreenchange, false );

						element.requestFullscreen = element.requestFullscreen || element.mozRequestFullscreen || element.mozRequestFullScreen || element.webkitRequestFullscreen;

						element.requestFullscreen();

					} else {

						element.requestPointerLock();

					}

				}, false );

			} else {

				instructions.innerHTML = 'Your browser doesn\'t seem to support Pointer Lock API';

			} */

			window.addEventListener("load", function() {
			    if (navigator.getVRDevices) {
			        navigator.getVRDevices().then(vrDeviceCallback);
			    } else if (navigator.mozGetVRDevices) {
			        navigator.mozGetVRDevices(vrDeviceCallback);
			    }
			}, false);

			function vrDeviceCallback(vrdevs) {
			    for (var i = 0; i < vrdevs.length; ++i) {
			        if (vrdevs[i] instanceof HMDVRDevice) {
			            vrHMD = vrdevs[i];
			            break;
			        }
			    }
			    for (var i = 0; i < vrdevs.length; ++i) {
			        if (vrdevs[i] instanceof PositionSensorVRDevice &&
			            vrdevs[i].hardwareUnitId == vrHMD.hardwareUnitId) {
			            vrHMDSensor = vrdevs[i];
			            break;
			        }
			    }
			    //initScene();
			    init();
   		 		//initRenderer();
    			animate();
			}
			function init() {

				scene = new THREE.Scene();

				//Black fog, helps for depth perception
				scene.fog = new THREE.Fog( 0x050505, 2000, 3500 );


				//Set the FOV, Angles
				camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 1, 5000);

				//So we can view the data headon
				camera.position.set(500,500,1500);

				//Used with oculus
				//camera.up = new THREE.Vector3(0,0,1);
				//camera.lookAt(new THREE.Vector3(0,0,0));
				//

				renderCanvas = document.getElementById("render-canvas");
				renderer = new THREE.WebGLRenderer(
					{canvas: renderCanvas,
						antialias: true}
				);
				renderer.setClearColor( scene.fog.color, 1 );
				renderer.setSize( window.innerWidth, window.innerHeight );
				vrrenderer = new THREE.VRRenderer(renderer, vrHMD);

				//Create the oculus render effect
				effect = new THREE.OculusRiftEffect(renderer, {worldScale: 200} );
				effect.setSize( window.innerWidth, window.innerHeight );

				document.body.appendChild( renderer.domElement );


				//Controls to move in the data
				//controls = new THREE.PointerLockControls( camera );
				//scene.add( controls.getObject() );

				raycaster = new THREE.Raycaster( new THREE.Vector3(), new THREE.Vector3( 0, - 1, 0 ), 0, 10 );

				//Used for oculus
				controls = new THREE.FirstPersonControls( camera );
				controls.movementSpeed = 10000;
				controls.lookSpeed = 0.0125;
				controls.lookVertical = true;
				//controls.addEventListener( 'change', render );
				//

				scene.add( new THREE.AmbientLight( 0x444444 ) );


				//This is hardcoded should be changed with a json Call
				var particleCount = 18540;
				particles = [];
				var geometry = new THREE.BufferGeometry();
				var geometry2 = new THREE.Geometry();

				positions = new Float32Array(particleCount*3);
				colors = [];

				var i =0;
				var zCount = 0;
				var xCount = 0;

				var maxValue = 0;

				$.ajax({
				    async: false,
				    url: "maxData.php",
				    success: function(json) {
				    	var data = JSON.parse(json);
				       maxValue = parseFloat(data['value']);
				       console.log(data['value']);
				    }
				});



				//Fetch data from JSON
				$.getJSON( "data.php", function( data ) {
					//Move each SiteID by 1 X val
					xCount = 0
				  $.each( data, function( key, val ) {
				  	//Each day is behind the Z axis
				  	zCount = 0;
				  	$.each(val, function(keyDate, valVal){
				  		//Set the XYZ coordinates
				  		positions[i] = xCount;
				  		positions[i+1] = parseFloat(valVal)/100;
				  		positions[i+2] = zCount;

				  		//
				  		var vertex = new THREE.Vector3();
						vertex.x = xCount;
						vertex.y = parseFloat(valVal)/100;
						vertex.z = zCount;
						geometry2.vertices.push( vertex );


				  		//Generate a random color -> change to colourmap of Y val
				  		//colors[i] = 1 - Math.cos(parseFloat(valVal)/100 / 255);
						//colors[i+1] = 1 -Math.sin(parseFloat(valVal)/100 / 255);
						//colors[i+2] = 1 -Math.cos(parseFloat(valVal)/100 / 255);

						colors[ i ] = new THREE.Color( 0xffffff );
						//colors[ i ].setHSL( Math.cos(parseFloat(valVal)/500), Math.sin(parseFloat(valVal)/500), 1 );
						colors[ i ].set(RainbowColor(parseFloat(valVal), maxValue));

				  		zCount = zCount-50;
				  		i = i+1;
				  	});
				  	xCount = xCount+3;
				  });


				sprite = new THREE.ImageUtils.loadTexture( "particle.png" );
				var pMaterial = new THREE.PointCloudMaterial({
				  //color: 0xFF0000,
				  vertexColors: THREE.VertexColors,
				  size: 15,
				  map: sprite,
				  transparent: true
				});
				pMaterial.color.setHSL( 1.0, 0.2, 0.7 );
					geometry2.colors = colors;
				  	
				  	geometry.addAttribute('position', new THREE.BufferAttribute(positions, 3));
					geometry.addAttribute('color', new THREE.BufferAttribute(colors, 3));
					//geometry.computeBoundingSphere();

					//var material = new THREE.PointCloudMaterial( { size: 15, vertexColors: THREE.VertexColors } );

					particleSystem = new THREE.PointCloud(geometry2, pMaterial );
					particleSystem.sortParticles = true;
					scene.add( particleSystem );
				});
			
				//Set stats div for FPS counter
				stats = new Stats();
				stats.domElement.style.position = 'absolute';
				stats.domElement.style.top = '0px';
				document.body.appendChild(stats.domElement);

				var helper = new THREE.GridHelper( 5000, 50 );
				helper.setColors( 0x0000ff, 0x808080 );
				helper.position.y = 0;
				helper.position.x = 500;
				helper.position.z = -500;
				scene.add( helper );

				//
				window.addEventListener( 'resize', onWindowResize, false );

				

			}


			function RainbowColor(length, maxLength)
			{
			    var i = (length * 255 / maxLength);
			    var r = Math.round(Math.sin(0.014 * i + 0) * 127 + 128);
			    var g = Math.round(Math.sin(0.014 * i + 2) * 127 + 128);
			    var b = Math.round(Math.sin(0.014 * i + 4) * 127 + 128);
			    return 'rgb(' + r + ',' + g + ',' + b + ')';
			}

			function onWindowResize() {

				camera.aspect = window.innerWidth / window.innerHeight;
				camera.updateProjectionMatrix();

				renderer.setSize( window.innerWidth, window.innerHeight );
				effect.setSize(window.innerWidth, window.innerHeight);

			}

			//

			function animate() {

				requestAnimationFrame( animate );

				//controls.isOnObject( false );

				//raycaster.ray.origin.copy( controls.getObject().position );
				//raycaster.ray.origin.y -= 10;

				//var intersections = raycaster.intersectObjects( objects );

				//if ( intersections.length > 0 ) {

					//controls.isOnObject( true );

				//}
				render();
				stats.update();
			}

			function render()
			{
				//raycaster.ray.origin.copy( controls.getObject().position );
				//raycaster.ray.origin.y -= 10;

				//var intersections = raycaster.intersectObjects( objects );

				//renderer.render(scene, camera);

					var state = vrHMDSensor.getState();
				   camera.quaternion.set(state.orientation.x, 
				                          state.orientation.y, 
				                          state.orientation.z, 
				                          state.orientation.w);


				vrrenderer.render(scene, camera);
				controls.update(clock.getDelta());

				//Enable for oculus
				//effect.render(scene, camera);
			}

			window.addEventListener("keypress", function(e) {
			    if (e.charCode == 'e'.charCodeAt(0)) {
			        if (renderCanvas.mozRequestFullScreen) {
			            renderCanvas.mozRequestFullScreen({
			                vrDisplay: vrHMD
			            });
			        } else if (renderCanvas.webkitRequestFullscreen) {
			            renderCanvas.webkitRequestFullscreen({
			                vrDisplay: vrHMD,
			            });
			        }
			    }
			}, false);
		</script>

	</body>
</html>
