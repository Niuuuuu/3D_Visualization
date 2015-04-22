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

				background-color: #000000;
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
				<br />
				(SPACE = Reset camera direction)
				<br />
				Press escape again to return to this screen and view cursor
				<br />
				to select data points
			</div>

		</div>

		<script src="../js/three.min.js"></script>
		<script src="../js/effects/oculus.js"></script>
		<script src="../js/effects/TrackballControls.js"></script>
		<script src="../js/effects/PointerLockControls.js"></script>
		<script src="../js/effects/FirstPersonControls.js"></script>
		<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
		<script src="../js/Detector.js"></script>
		<script src="../js/libs/stats.min.js"></script>
		<script src="../js/VRRenderer.js"></script>
		<script src="../js/VRControls.js"></script>
		<script src="../js/simple_statistics.js"></script>
		<script src="../js/DAT.GUI.js"></script>

		<script src="../js/Projector.js"></script>

		<!--ADDED-->
		<script src="http://mrdoob.github.com/three.js/examples/fonts/helvetiker_regular.typeface.js"></script>


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

			//ADDED
			var camdefaultangle;
			var recenterCamera = false;
			var lastPosition;
			var increasing = 0;
			var isControlsEnabled = true;
			var group;
			var lastRotation;
			var raycaster;
			var mouse;
			var container;

			var objects = [];
			var infoStrings = [];
			var selected = [];
			var meshArray = [];
			var coordsx = [];
			var coordsy = [];
			var coordsz = [];
			var kpiColors = [];

			var camera2;
			//ENDED ADDING

			//Needed variables to setup the scene

			var camera, scene, renderer;
			var vrrenderer;
			var controls;
			var particleSystem;


			//This is used for the FPS counter in the top left
			var stats;

			//Two div elements
			var blocker = document.getElementById( 'blocker' );
			var instructions = document.getElementById( 'instructions' );

			// VR Globals
			var vrEnabled = false;
			var vrHMD = null;
			var vrSensor = null;

			//ADDED
			document.addEventListener( 'keypress', onDocumentKeyPress, false );
			document.addEventListener( 'keydown', onDocumentKeyDown, false );
			document.addEventListener( 'click', onMouseClick, false );
			document.addEventListener( 'touchstart', onDocumentTouchStart, false );
			//ENDED ADDING
			
			//If we use the VR, dont pointer lock
			if(usingVR == false)
			{
				$.getScript("../PointerLockImplementor.js");
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



				//Creates a group to hold text sprites
				group = new THREE.Object3D();
				group.position.y = 10;
				group.position.x = 640;
				group.position.z = 40;
				scene = new THREE.Scene();
				//Black fog, helps for depth perception
				scene.fog = new THREE.Fog( 0x050505, 2000, 3500 );

				//ADDED
				//Set the FOV, Angles
				//camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 1, 5000);



				//So we can view the data headon - This should probably be switched to something dynamic
				//camera.position.set(500,500,1500);

				camera = new THREE.PerspectiveCamera( 70, window.innerWidth / window.innerHeight, 1, 10000 );
				camera.position.set( 100, 300, 500 );

				//camera.lookAt( new THREE.Vector3( camdefaultangle));
				//ADDED
				//centerScreen();
				camera2 = new THREE.PerspectiveCamera( 70, window.innerWidth / window.innerHeight, 1, 10000 );
				camera2.position.set( 100, 300, 500 );
				raycaster = new THREE.Raycaster();
				mouse = new THREE.Vector2();
				//ENDED ADDING


				renderer = new THREE.WebGLRenderer({clearAlpha: 1, alpha:true});
				//renderer.setClearColor( scene.fog.color, 1 );
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
					controls = new THREE.PointerLockControls(camera );
					scene.add( controls.getObject() );
				}
				lastPosition = new THREE.Vector3(0,0,0);
				lastRotation = new THREE.Vector3(0,0,0);

				scene.add( new THREE.AmbientLight( 0x444444 ) );

				//This is hardcoded should be changed with a json Call
				var particleCount = 18540;
				particles = [];
				var geometry = new THREE.BufferGeometry();
				var geometry2 = new THREE.Geometry();
				var boxgeometry = new THREE.SphereGeometry( 15, 10, 10 );

				positions = new Float32Array(particleCount*3);
				colors = [];

				var i =0;
				var zCount = 0;
				var xCount = 0;

				var maxValue = 5000;
				var myData_X = [];
				var myData_Y = [];
				var myData_Z = [];

				$.post( "api/threeAxisData", '<?php echo $data; ?>')
				.done(function( data ) {
					data = JSON.parse(data);
					$.each(data, function(siteName, coords)
					{


				  		colors[ i ] = new THREE.Color( 0x0000ee );
						//colors[ i ].set(RainbowColor(parseFloat(coords.z), maxValue));
						//ADDED
						var object = new THREE.Mesh( boxgeometry, new THREE.MeshBasicMaterial( { color: colors[i], opacity: 0.5 } ) );
						object.position.x = coords.x;
						object.position.y = coords.y;
						object.position.z = coords.z;
						object.id = i;
						var id = object.id;
						kpiColors[id] = colors[i].getHex();

						selected[id] = false;
						coordsx[id] = coords.x;
						coordsy[id] = coords.y;
						coordsz[id] = coords.z;

						infoStrings[id] = "";
						infoStrings[id] = "Site Name: " + siteName;
						infoStrings[id] += ", x :" + parseFloat(coords.x);
						infoStrings[id] += ", y :" + parseFloat(coords.y);
						infoStrings[id] += ", z :" + parseFloat(coords.z);


					

						scene.add(object);
						objects.push(object);
						//drawn = true;
						//DONE ADDING


						var vertex = new THREE.Vector3();
						vertex.x = coords.x;
						vertex.y = coords.y;
						vertex.z = coords.z;
						geometry2.vertices.push( vertex );

						myData_X[i] =  parseFloat(coords.x);
						myData_Y[i] =  parseFloat(coords.y);
						myData_Z[i] =  parseFloat(coords.z);

						console.log(vertex);
						console.log(myData_X[i]);

						positions[i] = xCount; 
				  		positions[i+1] = parseFloat(coords.y)/100;
				  		positions[i+2] = zCount/100;

				  		i = i+1;
					});

					

				    guiBox(myData_X,myData_Y,myData_Z);

				    
				 	//sprite info
				 	sprite = new THREE.ImageUtils.loadTexture( "../disc.png" );
					var pMaterial = new THREE.PointCloudMaterial({
					  //color: 0xFF0000,
					  vertexColors: THREE.VertexColors,
					  size: 50,
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

						



					    

				//labels facing camera
				var spritey = makeTextSprite( "<?php echo $labels['xAxis']; ?>+ ", 
						{ fontsize: 25, borderColor: {r:255, g:0, b:0, a:1.0}, backgroundColor: {r:255, g:100, b:100, a:0.5} } );
						spritey.position.set(500,10,10);
						scene.add( spritey );

				var spritey = makeTextSprite( "<?php echo $labels['yAxis']; ?>+ ", 
						{ fontsize: 25 , borderColor: {r:0, g:0, b:255, a:1.0}, backgroundColor: {r:100, g:100, b:255, a:0.5} } );
						spritey.position.set(10,500,10);
						scene.add( spritey );

				var spritey = makeTextSprite( "<?php echo $labels['zAxis']; ?>+ ", 
						{ fontsize: 25 , borderColor: {r:0, g:255, b:0, a:1.0}, backgroundColor: {r:100, g:255, b:100, a:0.8} } );
						spritey.position.set(10,10,500);
						scene.add( spritey );
				var spritey = makeTextSprite( "<?php echo $labels['xAxis']; ?>- ", 
						{ fontsize: 25, borderColor: {r:255, g:0, b:0, a:1.0}, backgroundColor: {r:255, g:100, b:100, a:0.5} } );
						spritey.position.set(-500,10,10);
						scene.add( spritey );

				var spritey = makeTextSprite( "<?php echo $labels['yAxis']; ?>- ", 
						{ fontsize: 25 , borderColor: {r:0, g:0, b:255, a:1.0}, backgroundColor: {r:100, g:100, b:255, a:0.5} } );
						spritey.position.set(10,-500,10);
						scene.add( spritey );

				var spritey = makeTextSprite( "<?php echo $labels['zAxis']; ?>- ", 
						{ fontsize: 25 , borderColor: {r:0, g:255, b:0, a:1.0}, backgroundColor: {r:100, g:255, b:100, a:0.8} } );
						spritey.position.set(10,10,-500);
						scene.add( spritey );







				});



			
				//Set stats div for FPS counter
				stats = new Stats();
				stats.domElement.style.position = 'absolute';
				stats.domElement.style.top = '0px';
				document.body.appendChild(stats.domElement);

				var helper = new THREE.GridHelper( 5000, 50 );
				helper.setColors( 0x0000ff, 0x808080 );
				helper.position.y = 0;
				helper.position.x = 0;
				helper.position.z = 0;
				scene.add( helper );

				//
				window.addEventListener( 'resize', onWindowResize, false );

				
				
			}




			// select the quantile 
			// One definition of outlier is any data point more than 1.5 interquartile ranges (IQRs) 
			// below the first quartile or above the third quartile, so setting lowerlimit/upperlimit
			// to 0.25 and 0.75 will be able to spot standard outliers 
			function guiBox(myData_X,myData_Y,myData_Z){
				
				
				var bbox1,bbox2,bbox3,bbox4,bbox5,bbox6,bbox7,bbox8,bmesh;
				var bboxExist = false;


				var effectController = {
						boxVisible : ! true ,
						boxCoordinate: ! true,
						lowerQuantile : 0.1,
						upperQuantile : 0.9,
						lineOnBox : 2,
						boxcolor: 0x88DA5


				}




			
				function boundConfig(){

					if (bboxExist){
						
							scene.remove(bmesh);
							scene.remove(bbox1);
							scene.remove(bbox2);
							scene.remove(bbox3);
							scene.remove(bbox4);
							scene.remove(bbox5);
							scene.remove(bbox6);
							scene.remove(bbox7);
							scene.remove(bbox8);
							console.log("scene removed");
						
						bboxExist = false;
					}



					if (!bboxExist){


							//store the lower/upper limit in vector
							var min = new THREE.Vector3(ss.quantile(myData_X, effectController.lowerQuantile),
							ss.quantile(myData_Y,  effectController.lowerQuantile),ss.quantile(myData_Z, effectController.lowerQuantile));

							var max = new THREE.Vector3(ss.quantile(myData_X, effectController.upperQuantile),
							ss.quantile(myData_Y, effectController.upperQuantile),ss.quantile(myData_Z, effectController.upperQuantile));

							console.log (min,max);

							var bgeometry = new THREE.BoxGeometry(max.x - min.x , 
																  max.y - min.y, 
																  max.z - min.z,
																  effectController.lineOnBox,
													    		  effectController.lineOnBox,
																  effectController.lineOnBox);
				 			
				 			var bmaterial = new THREE.MeshBasicMaterial({
								        color: effectController.boxcolor,
								        wireframe: true
							});

				 			bmesh = new THREE.Mesh(bgeometry, bmaterial);
							bmesh.position.set(  min.x+(max.x - min.x)/2,
								    			 min.y+(max.y - min.y)/2,
								    			 min.z+(max.z - min.z)/2  );

							scene.add(bmesh);
							


							

						bbox1 = makeTextSprite(  "("+ parseInt(min.x) + "," + parseInt(min.y) + "," + parseInt(min.z) + ")", 
						{ fontsize: 30, borderColor: {r:255, g:0, b:0, a:0}, backgroundColor: {r:255, g:100, b:100, a:0}, textColor : "rgba(23,30,40, 1.0)" } );
						bbox1.position.set(min.x ,min.y, min.z);
						scene.add( bbox1 );
				
						bbox2 = makeTextSprite(  "("+ parseInt(max.x) + "," + parseInt(min.y) + "," + parseInt(min.z) + ")", 
						{ fontsize: 30, borderColor: {r:255, g:0, b:0, a:0}, backgroundColor: {r:255, g:100, b:100, a:0}, textColor : "rgba(23,30,40, 1.0)" } );
						bbox2.position.set( max.x ,min.y, min.z);
						scene.add( bbox2 );

						bbox3 = makeTextSprite(  "("+ parseInt(min.x) + "," + parseInt(max.y) + "," + parseInt(min.z) + ")", 
						{ fontsize: 30, borderColor: {r:255, g:0, b:0, a:0}, backgroundColor: {r:255, g:100, b:100, a:0}, textColor : "rgba(23,30,40, 1.0)" } );
						bbox3.position.set( min.x ,max.y, min.z);
						scene.add( bbox3 );

						bbox4 = makeTextSprite(  "("+ parseInt(max.x) + "," + parseInt(max.y) + "," + parseInt(min.z) + ")", 
						{ fontsize: 30, borderColor: {r:255, g:0, b:0, a:0}, backgroundColor: {r:255, g:100, b:100, a:0}, textColor : "rgba(23,30,40, 1.0)" } );
						bbox4.position.set( max.x ,max.y, min.z);
						scene.add( bbox4 );

						bbox5 = makeTextSprite(  "("+ parseInt(min.x) + "," + parseInt(min.y) + "," + parseInt(max.z) + ")", 
						{ fontsize: 30, borderColor: {r:255, g:0, b:0, a:0}, backgroundColor: {r:255, g:100, b:100, a:0}, textColor : "rgba(23,30,40, 1.0)" } );
						bbox5.position.set( min.x ,min.y, max.z);
						scene.add( bbox5 );

						bbox6 = makeTextSprite(  "("+ parseInt(max.x) + "," + parseInt(min.y) + "," + parseInt(max.z) + ")", 
						{ fontsize: 30, borderColor: {r:255, g:0, b:0, a:0}, backgroundColor: {r:255, g:100, b:100, a:0}, textColor : "rgba(23,30,40, 1.0)" } );
						bbox6.position.set( max.x ,min.y, max.z);
						scene.add( bbox6 );

						bbox7 = makeTextSprite(  "("+ parseInt(min.x) + "," + parseInt(max.y) + "," + parseInt(max.z) + ")", 
						{ fontsize: 30, borderColor: {r:255, g:0, b:0, a:0}, backgroundColor: {r:255, g:100, b:100, a:0}, textColor : "rgba(23,30,40, 1.0)" } );
						bbox7.position.set( min.x ,max.y, max.z);
						scene.add( bbox7 );

					    bbox8 = makeTextSprite(  "("+ parseInt(max.x) + "," + parseInt(max.y) + "," + parseInt(max.z) + ")", 
						{ fontsize: 30, borderColor: {r:255, g:0, b:0, a:0}, backgroundColor: {r:255, g:100, b:100, a:0}, textColor : "rgba(23,30,40, 1.0)" } );
						bbox8.position.set( max.x ,max.y, max.z);
						scene.add( bbox8 );
				







							bboxExist = true;
							console.log("scene added");
					    }

					 

					bbox8.visible = bbox7.visible = bbox6.visible = bbox5.visible = 
					bbox4.visible = bbox3.visible = bbox2.visible =  bbox1.visible = effectController.boxCoordinate;
					
					bmesh.visible = effectController.boxVisible;


				}


				var gui = new DAT.GUI({

					height : 5*32 -1


				}
					);
				gui.add(effectController, "boxVisible").onChange(boundConfig);
				gui.add(effectController, "boxCoordinate").onChange(boundConfig);
				gui.add (effectController, "lowerQuantile", 0.0, 0.5, 0.025	).onChange(boundConfig);
				gui.add (effectController, "upperQuantile", 0.5, 1.0, 0.025	).onChange(boundConfig);
				gui.add(effectController, "lineOnBox", 1,10,1 ).onChange(boundConfig);
				
				boundConfig();



			}

			//function to make text facing camera
			function makeTextSprite( message, parameters )
			{
				if ( parameters === undefined ) parameters = {};
				
				var fontface = parameters.hasOwnProperty("fontface") ? 
					parameters["fontface"] : "Arial";
				
				var fontsize = parameters.hasOwnProperty("fontsize") ? 
					parameters["fontsize"] : 18;
				
				var borderThickness = parameters.hasOwnProperty("borderThickness") ? 
					parameters["borderThickness"] : 4;
				
				var borderColor = parameters.hasOwnProperty("borderColor") ?
					parameters["borderColor"] : { r:0, g:0, b:0, a:1.0 };
				
				var backgroundColor = parameters.hasOwnProperty("backgroundColor") ?
					parameters["backgroundColor"] : { r:255, g:255, b:255, a:1.0 };

				var spriteAlignment = THREE.SpriteAlignment;
					
				var canvas = document.createElement('canvas');
				var context = canvas.getContext('2d');
				context.font =  fontsize + "px " + fontface;
			    
				// get size data (height depends only on font size)
				var metrics = context.measureText( message );
				var textWidth = metrics.width;
				
				// background color
				/*context.fillStyle   = "rgba(" + backgroundColor.r + "," + backgroundColor.g + ","
											  + backgroundColor.b + "," + backgroundColor.a + ")";
				// border color
				context.strokeStyle = "rgba(" + borderColor.r + "," + borderColor.g + ","
											  + borderColor.b + "," + borderColor.a + ")";*/

				//context.lineWidth = borderThickness;
				//roundRect(context, borderThickness/2, borderThickness/2, textWidth + borderThickness, fontsize * 1.4 + borderThickness, 6);
				// 1.4 is extra height factor for text below baseline: g,j,p,q.
				
				// text color
				context.fillStyle = parameters.hasOwnProperty("textColor") ?
				parameters["textColor"]: "rgba(0, 0, 0, 1.0)";

				context.fillText( message, borderThickness, fontsize + borderThickness);
				
				// canvas contents will be used for a texture
				var texture = new THREE.Texture(canvas) 
				texture.needsUpdate = true;

				var spriteMaterial = new THREE.SpriteMaterial( 
					{ map: texture, useScreenCoordinates: false } );
				var sprite = new THREE.Sprite( spriteMaterial );
				sprite.scale.set(500,250,1.0);
				return sprite;	
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


			// ADDED STUFF HERE
			function onMouseClick( event ){
				
       			event.preventDefault();

				mouse.x = ( event.clientX / window.innerWidth ) * 2 - 1;
				mouse.y = - ( event.clientY / window.innerHeight ) * 2 + 1;
				console.log(mouse.x + "   " + mouse.y + " Objectssize: " + objects.length);
				raycaster.setFromCamera( mouse, camera2 );

				console.log( "SCENEX:" + camera2.position.x + "SCENEY: " + camera2.position.y + "SCENEZ: " + camera2.position.z);
				console.log( "SCENEX:" + lastPosition.x + "SCENEY: " + lastPosition.y + "SCENEZ: " + lastPosition.z);
				//console.log( "changedpositionx:" + controls.getObject().position.x + "chy: " + controls.getObject().position.y + "chz: " + controls.getObject().position.z);

				var intersects = raycaster.intersectObjects( objects );
				console.log("Intersects lengths: " + intersects.length);

				if ( intersects.length > 0 ) {
					var i = intersects[0].object.id;
					console.log("Something detected:: " + i);
					if(selected[i]){
						intersects[ 0 ].object.material.color.setHex(kpiColors[i]);

						console.log("Deselected i");
						group.remove(meshArray[i]);
						selected[i] = false;
					}else{
						//kpiColors[i] = intersects[ 0 ].object.material.color;
						intersects[ 0 ].object.material.color.setHex( 0xF234CD );

						console.log("Selected i");
				  		var canvas2 = document.createElement('canvas');
				  		canvas2.width = 1200;
				  		canvas2.height = 45;

						var context2 = canvas2.getContext('2d');
						context2.font = "40px Helvetica Neue";
						context2.fillStyle = "rgba(0,0,0,0.95)";

						context2.fillText(infoStrings[i], 0, 40);
						console.log(infoStrings[i]);



					    var geod= new THREE.Geometry(canvas2.width, canvas2.height);

						var texture2 = new THREE.Texture(canvas2) 
						texture2.needsUpdate = true;

			    		var material2 = new THREE.MeshBasicMaterial( {map: texture2, side:THREE.DoubleSide } );		


						var mesh2 = new THREE.Mesh(
					        new THREE.PlaneBufferGeometry(canvas2.width, canvas2.height),
					        material2
					    );

						//mesh2.position.set(0,0,0);
						mesh2.position.set(coordsx[i],coordsy[i],coordsz[i]);
	

						meshArray[i]= mesh2;
						group.add( meshArray[i]);
						selected[i] = true;
					}
					scene.add(group);
					//scene.add(meshArray[i]);
				}

				
				console.log("x: " + event.x + "y: " + event.y);
			}


			function onDocumentTouchStart( event ) {
				
				event.preventDefault();
				
				event.clientX = event.touches[0].clientX;
				event.clientY = event.touches[0].clientY;
				onDocumentMouseDown( event );

			}
			function onDocumentKeyDown( event ) {

			}

			//
			function onDocumentKeyPress( event ) {

				var keyCode = event.which;
				if(keyCode == 106){
					
				}

				if(keyCode == 49){
					//controls.enabled != controls.enabled;
					//PointerLockControls.enabled != PointerLockControls.enabled;]
					console.log("Disable controls");
					controls.enabled != controls.enabled;
					if(isControlsEnabled){
						scene.remove(controls.getObject());
						isControlsEnabled = false;
					}
					else{
						scene.add(controls.getObject());
						isControlsEnabled = true;
					}
				}
				

				if(keyCode == 32){
					centerScreen();
				}
				

				// backspace
				console.log("This is" + keyCode);

			}

			function centerScreen(){
				//camera.target.set( camdefaultangle );
				//controls.enabled = false;
				//controls.d
				console.log( "SCENEX:" + controls.getObject().position.x + "SCENEY: " + controls.getObject().position.y + "SCENEZ: " + controls.getObject().position.z);
				console.log( "SCENEX:" + controls.getObject().rotation.x + "SCENEY: " + controls.getObject().rotation.y + "SCENEZ: " + controls.getObject().rotation.z);

				//camera.up = new THREE.Vector3(0,0,1);
				//recenterCamera = true;
				//camera.position.set(-increasing,-500,1500);
				//camera.lookAt( camdefaultangle );
				camera.updateProjectionMatrix();

				controls.getObject().children[0].rotation.x = 0;
				controls.getObject().rotation.y = 0.0;

			}


//ENDED ADDDING STUFF

			function animate() {
				requestAnimationFrame( animate );
				//controls.isOnObject( false );
				render();
				stats.update();
			}

			function render()
			{
				controls.update();

				//ADDED. This code here moves the camera to reflect the movement in the scene
				//in order to be able to select data points!
				if(	lastPosition.x != controls.getObject().position.x || lastPosition.y != controls.getObject().position.y || lastPosition.z != controls.getObject().position.z){
				camera2.position.x += controls.getObject().position.x - lastPosition.x;
				camera2.position.y += controls.getObject().position.y - lastPosition.y;
				camera2.position.z += controls.getObject().position.z - lastPosition.z;
				//console.log( "changedpositionx:" + camera2.position.x + "chy: " + camera2.position.y + "chz: " + camera2.position.z);
				lastPosition.x = controls.getObject().position.x;
				lastPosition.y = controls.getObject().position.y;
				lastPosition.z = controls.getObject().position.z;
				//camera2.position = controls.getObject().position;

				}

			
				//console.log( "SCENEX:" + controls.getObject().rotation.x + "SCENEY: " + controls.getObject().rotation.y + "SCENEZ: " + controls.getObject().rotation.z);
*/
				var yaw = controls.getObject().rotation.y;
				var pitch = controls.getObject().children[0].rotation.x;


				camera2.rotation.y = yaw;

				
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
					renderer.render(scene, camera2);
				}

			}

		</script>

	</body>
</html>
