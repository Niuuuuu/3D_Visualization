<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Three.JS Telus</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
		<style>
			body {
			    margin:0;
			    background-color: #f0f0f0;
				margin: 0px;
				overflow: hidden;
			}

			#render-canvas {
			    width: 100%;
			    height: 100%;
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

		<canvas id="render-canvas"></canvas>
		<center><div id="info"></div></center>
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
		<script src="js/controls/OculusControls.js"></script>
		<script src="js/VRRenderer.js"></script>
		<script src="js/VRControls.js"></script>
		<script src="js/orbitcontrols.js"></script>
		<script src="js/CustomControls.js"></script>
		<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>

		<script src="http://mrdoob.github.com/three.js/examples/fonts/helvetiker_regular.typeface.js"></script>

		<script src="js/Detector.js"></script>
		<script src="js/libs/stats.min.js"></script>

		<script>

			//Used to enable Oculus effects or not, this changes the renderer, controls etc.
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

			//Three.JS elements
			var camera, scene, renderer;
			var pitchObject, yawObject;
			var controls;
			var mesh;
			var totalMerge;
			var vrrenderer;
			var clock = new THREE.Clock();


			//Div Elements
			var container, stats;
			var blocker = document.getElementById( 'blocker' );
			var instructions = document.getElementById( 'instructions' );
			var renderCanvas = document.getElementById("render-canvas");


			// VR Globals
			var vrEnabled = false;
			var vrHMD = null;
			var vrSensor = null;

			//Constants
			var PI_2 = Math.PI / 2;


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

				//Create the Scene
				scene = new THREE.Scene();

				//Black fog, helps for depth perception
				scene.fog = new THREE.Fog( 0x050505, 2000, 4000 );


				//Set the FOV, Angles
				camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 100, 20000);

				//So we can view the data headon
				camera.position.set(500,500,1500);

				pitchObject = new THREE.Object3D();
				pitchObject.add( camera );

				yawObject = new THREE.Object3D();
				yawObject.position.y = 500;
				yawObject.position.x = 500;
				yawObject.position.z = 1500;
				yawObject.add( pitchObject );

				scene.add(yawObject);


				//Create a renderer to display the output
				renderCanvas = document.getElementById("render-canvas");
				renderer = new THREE.WebGLRenderer(
					{canvas: renderCanvas,
						antialias: true}
				);
				renderer.setClearColor( scene.fog.color, 1 );
				renderer.setSize( window.innerWidth, window.innerHeight );

				if(usingVR)
				{
					vrrenderer = new THREE.VRRenderer(renderer, vrHMD);
				}

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
				
				scene.add( new THREE.AmbientLight( 0xffffff ) );

				var maxValue = 0;

				$.ajax({
				    async: false,
				    url: "maxData.php",
				    success: function(json) {
				    	var data = JSON.parse(json);
				       maxValue = parseFloat(data['value']);
				    }
				});

				//This is hardcoded should be changed with a json Call
				var particleCount = 18540;
				particles = [];

				positions = new Float32Array(particleCount*3);
				colors = [];

				var i =0;
				var zCount = 0;
				var xCount = 0;

				var materials = [];

				totalMerge = new THREE.Geometry();
				var geo = new THREE.Geometry();


				var geometry = new THREE.BoxGeometry(5, 1, 5);
				geometry.applyMatrix( new THREE.Matrix4().makeTranslation( 0, 0.5, 0 ) );
				var mesh = new THREE.Mesh( geometry );

				//Fetch data from JSON
				$.getJSON( "data.php", function( data ) {
					//Move each SiteID by 1 X val
					xCount = 0
				  $.each( data, function( key, val ) {
				  	
				  	//Each day is behind the Z axis
				  	zCount = 0;
				  	$.each(val, function(keyDate, valVal){

				  		mesh.position.x = xCount;
				  		mesh.position.z = zCount;

				  		//(xCount, parseFloat(valVal)/10000, zCount);
				  		mesh.scale.y = parseFloat(valVal)/100;
				  		//geometry.setY = parseFloat(valVal)/100;

				  		THREE.GeometryUtils.merge(totalMerge, mesh);

							var baseColor = new THREE.Color(RainbowColor(parseFloat(valVal), maxValue));

						    // For each face of the cube, I assign the color
							    for ( var j = 0; j < geometry.faces.length; j ++ ) {
							        mesh.geometry.faces[ j ].color = baseColor;
								}

				  		zCount = zCount-50;
				  		i = i+1;

				  	});

						var canvas1 = document.createElement('canvas');
						var context1 = canvas1.getContext('2d');
						context1.font = "40px Helvetica Neue";
						context1.fillStyle = "rgba(223,116,12,1)";
					    context1.fillText(key, 0, 50);
					    
						// canvas contents will be used for a texture
						var texture1 = new THREE.Texture(canvas1) 
						texture1.needsUpdate = true;
					      
					    var material1 = new THREE.MeshBasicMaterial( {map: texture1, side:THREE.DoubleSide } );
					    material1.transparent = true;

					    var mesh1 = new THREE.Mesh(
					        new THREE.PlaneBufferGeometry(canvas1.width, canvas1.height),
					        material1
					    );
						mesh1.position.set(xCount,0,0);
						mesh1.rotation.x = -Math.PI / 2;
						mesh1.rotation.z = Math.PI / 2;

						scene.add( mesh1 );


				  	xCount = xCount+50;
				  });
					
					totalMerge.computeFaceNormals();
					var material  = new THREE.MeshBasicMaterial({
					  vertexColors    : true,
					  wireframe		: false,
					});
					var total = new THREE.Mesh(totalMerge, material);
					total.matrixAutoUpdate = false;
					total.updateMatrix();
					scene.add(total);
					
				});
			
				//Set stats div for FPS counter
				stats = new Stats();
				stats.domElement.style.position = 'absolute';
				stats.domElement.style.top = '0px';
				document.body.appendChild(stats.domElement);

				var helper = new THREE.GridHelper( 5000, 50 );
				helper.setColors( 0x6FC3DF, 0x6FC3DF );
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
			}


			function animate() {

				requestAnimationFrame( animate );
				render();
				stats.update();
			}

			function render()
			{
				controls.update(clock.getDelta());

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
