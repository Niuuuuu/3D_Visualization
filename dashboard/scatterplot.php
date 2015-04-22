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

			#blocker {

				position: absolute;

				width: 100%;
				height: 100%;

				background-color: rgba(0,0,0,0.5);

			}

			#instructions {

				width: 100%;
				height: 100%;

				display: -webkit-box;
				display: -moz-box;
				display: box;

				-webkit-box-orient: horizontal;
				-moz-box-orient: horizontal;
				box-orient: horizontal;

				-webkit-box-pack: center;
				-moz-box-pack: center;
				box-pack: center;

				-webkit-box-align: center;
				-moz-box-align: center;
				box-align: center;

				color: #ffffff;
				text-align: center;

				cursor: pointer;

			}

		</style>
	</head>
	<body>

		<div id="container"></div>
		<div id="info">ThreeJS Telus</div>

		<div id="blocker">

			<div id="instructions">
				<span style="font-size:40px">Click to view data</span>
				<br />
				(W, A, S, D = Move, R, F = Up/Down, MOUSE = Look around)
			</div>

		</div>

		<script src="js/three.min.js"></script>
		<script src="js/effects/oculus.js"></script>
		<script src="js/effects/TrackballControls.js"></script>
		<script src="js/effects/PointerLockControls.js"></script>
		<script src="js/effects/FirstPersonControls.js"></script>
		<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
		<script src="js/Detector.js"></script>
		<script src="js/libs/stats.min.js"></script>
		<script src="js/VRRenderer.js"></script>
		<script src="js/VRControls.js"></script>


		<script src="PointerLockImplementor.js"></script>

		<script>

			<?php 
				if(isset($_GET['useVR']))
				{
					echo "var usingVR = true;";
				}
				else
				{
					echo "var usingVR = false;";
				}
			?>

			//Needed variables to setup the scene
			var camera, scene, renderer;
			var vrrenderer;
			var controls;

			//This is used for the FPS counter in the top left
			var stats;

			//Two div elements
			var blocker = document.getElementById( 'blocker' );
			var instructions = document.getElementById( 'instructions' );

			// VR Globals
			var vrEnabled = false;
			var vrHMD = null;
			var vrSensor = null;

			
			//If we use the VR, dont pointer lock
			if(usingVR == false)
			{
				$.getScript("PointerLockImplementor.js");
				init();
				animate();
			}
			else
			{
				$( "#instructions" ).hide();
				$( "#blocker" ).hide();

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
				        	console.log(vrdevs[i]);
				            vrHMD = vrdevs[i];
				            break;
				        }
				    }
				    for (var i = 0; i < vrdevs.length; ++i) {
				        if (vrdevs[i] instanceof PositionSensorVRDevice &&
				            vrdevs[i].hardwareUnitId == vrHMD.hardwareUnitId) {
				        	console.log(vrdevs[i]);
				            vrHMDSensor = vrdevs[i];
				            break;
				        }
				    }
				    console.log(vrHMD);
				    init();
	    			animate();
				}
			}


			function init() {

				

				//Black fog, helps for depth perception
				scene = new THREE.Scene();
				scene.fog = new THREE.Fog( 0x050505, 2000, 3500 );


				//Set the FOV, Angles
				camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 1, 5000);


				//So we can view the data headon - This should probably be switched to something dynamic
				camera.position.set(500,500,1500);


				renderer = new THREE.WebGLRenderer({clearAlpha: 1, alpha:true});
				renderer.setClearColor( scene.fog.color, 1 );
				renderer.setSize( window.innerWidth, window.innerHeight );

				if(usingVR)
				{
					vrrenderer = new THREE.VRRenderer(renderer, vrHMD);
				}

				//Create the oculus render effect
				effect = new THREE.OculusRiftEffect(renderer, {worldScale: 200} );
				effect.setSize( window.innerWidth, window.innerHeight );

				document.body.appendChild( renderer.domElement );


				//If we're using the oculus, use the oculus controls, otherwise switch to Keyboard+Mouse
				if(usingVR)
				{
					controls = new THREE.VRControls(camera);
					controls = new THREE.FirstPersonControls( camera );
					controls.movementSpeed = 1000;
					controls.lookSpeed = 100;
					controls.lookVertical = true;
				}
				else
				{
					controls = new THREE.PointerLockControls( camera );
					scene.add( controls.getObject() );
				}


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

						colors[ i ] = new THREE.Color( 0xffffff );
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
				controls.isOnObject( false );
				render();
				stats.update();
			}

			function render()
			{
				controls.update();
				
				if(usingVR)
				{
					var state = vrHMDSensor.getState();
				   	camera.quaternion.set(state.orientation.x, 
				                          state.orientation.y, 
				                          state.orientation.z, 
				                          state.orientation.w);


					vrrenderer.render(scene, camera);
				}
				else
				{
					renderer.render(scene, camera);
				}

			}
		</script>

	</body>
</html>
