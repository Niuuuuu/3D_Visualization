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

		<script src="../js/three.min.js"></script>
		<script src="../js/effects/TrackballControls.js"></script>
		<script src="../js/effects/PointerLockControls.js"></script>
		<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
		<script src="../js/simple_statistics.js"></script>
		<script src="../js/DAT.GUI.js"></script>

		<script src="../js/Projector.js"></script>


		<script>
			//Required to setup the scene
			var scene;
			var camera;
			var renderer;
			var controls;
			var raycaster;
			var mouse;
			var testCount;

			var objects = [];
			var infoStrings = [];
			var selected = [];
			var meshArray = [];
			var coordsx = [];
			var coordsy = [];
			var coordsz = [];
			var kpiColors = [];
			var colors = [];

			//Used for dat
			var plot;
			var particleSystem;

			//Add a mouse click listener to web view
			document.addEventListener( 'click', onMouseClick, false );


			var bounds =
			{
			    'maxx' : 1000,
			    'minx' : -1000,
			    'maxy' : 1000,
			    'miny' : -1000,
			    'maxz' : 1000,
			    'minz' : -1000
			};

			var xAxisGeo = new THREE.Geometry();
			var yAxisGeo = new THREE.Geometry();
			var zAxisGeo = new THREE.Geometry();
			var boundaryGeo = new THREE.Geometry();

			function main() {
			    init();
			    console.log("Initialized!");
			    run();
			}

			function run()
			{
				requestAnimationFrame(run);
				render();
			}

			function init()
			{
				//Initialise the scene
				scene = new THREE.Scene();
				group = new THREE.Object3D();
				group.position.y = 30;
				group.position.x = 0;
				group.position.z = -20;

				//Place the camera in the correct place
				camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 1, 10000000);
				camera.position.z = bounds.maxz * 4;
			    camera.position.x = 0;
			    camera.position.y = bounds.maxy * 1.25;
			    camera.lookAt(scene.position);
			    scene.add(camera);

			    var light = new THREE.HemisphereLight( 0xffffff, 0xffffff, 1 ); // soft white light
				scene.add( light );

			    controls = new THREE.TrackballControls(camera);
			    raycaster = new THREE.Raycaster();

				
				//Plot will hold out "grid"
				plot = new THREE.Object3D();
				scene.add(plot);

				scene.fog = null;

				xAxisGeo.vertices.push(new THREE.Vector3(bounds.minx, 0, 0), new THREE.Vector3(bounds.maxx, 0, 0));
				yAxisGeo.vertices.push(new THREE.Vector3(0, bounds.miny, 0), new THREE.Vector3(0, bounds.maxy, 0));
				zAxisGeo.vertices.push(new THREE.Vector3(0, 0, bounds.minz), new THREE.Vector3(0, 0, bounds.maxz));

				//DRAWING LINES
				var xAxisMat = new THREE.LineBasicMaterial({color: 0xff0000, lineWidth: 1});
				var xAxis = new THREE.Line(xAxisGeo, xAxisMat);
				xAxis.type = THREE.Lines;
				plot.add(xAxis);

				var yAxisMat = new THREE.LineBasicMaterial({color: 0x0000ff, lineWidth: 1});
				var yAxis = new THREE.Line(yAxisGeo, yAxisMat);
				yAxis.type = THREE.Lines;
				plot.add(yAxis);

				var zAxisMat = new THREE.LineBasicMaterial({color: 0x00ff00, lineWidth: 1});
				var zAxis = new THREE.Line(zAxisGeo, zAxisMat);
				zAxis.type = THREE.Lines;
				plot.add(zAxis);

				//Init the renderer to display all of this
				renderer = new THREE.WebGLRenderer({clearAlpha: 1, alpha:true, antialiasing: true});
				renderer.setSize( window.innerWidth, window.innerHeight );
				document.body.appendChild( renderer.domElement );

				testCount = 0;
				
					


				var geometry = new THREE.Geometry();
				mouse = new THREE.Vector2();


				var i=0;
				var myData_X = [];
				var myData_Y = [];
				var myData_Z = [];

				var boxgeometry = new THREE.SphereGeometry( 15, 10, 10 );


				$.post( "api/threeAxisData", '<?php echo $data; ?>')
				.done(function( data ) {
					data = JSON.parse(data);
					$.each(data, function(siteName, coords)
					{

				  		colors[ i ] = new THREE.Color( 0xffaaa0 );
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

						//Looks like if a value is undefined across an axis the graph does not
						//plot the point with the value. So we set undefined to mean 0

						if(coordsz[id] == null){
							object.position.z = 0;
							coordsz[id] = 0;
						}if(coordsy[id] == null){
							object.position.y = 0;
							coordsy[id] = 0;
						}if(coordsx[id] == null){
							object.position.x = 0;
							coordsx[id] = 0;
						}

						infoStrings[id] = "";
						infoStrings[id] = "Site Name: " + siteName;
						infoStrings[id] += ", x :" + parseFloat(coords.x);
						infoStrings[id] += ", y :" + parseFloat(coords.y);
						infoStrings[id] += ", z :" + parseFloat(coords.z);


						scene.add(object);
						objects.push(object);
						testCount++;
						var vertex = new THREE.Vector3( coords.x, coords.y, coords.z );
						geometry.vertices.push(vertex);
						

						myData_X[i] =  parseFloat(coords.x);
						myData_Y[i] =  parseFloat(coords.y);
						myData_Z[i] =  parseFloat(coords.z);


						console.log(coords);
						console.log(myData_X[i]);
						i= i+1;
					});
					var sprite = THREE.ImageUtils.loadTexture("../disc.png");
					var material = new THREE.ParticleBasicMaterial({size: 15, depthTest: false, transparent: true, color :0xffaaa0, map: sprite});
					particleSystem = new THREE.ParticleSystem(geometry, material);
					plot.add(THREE.ParticleSystem( geometry, material));



				guiBox(myData_X,myData_Y,myData_Z);

				});



	

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


			}

				

			function guiBox(myData_X,myData_Y,myData_Z){
				
				
				var bbox1,bbox2,bbox3,bbox4,bbox5,bbox6,bbox7,bbox8,bmesh;
				var bboxExist = false;


				var effectController = {
						boxVisible : ! true ,
						boxCoordinate: ! true,
						lowerQuantile : 0.1,
						upperQuantile : 0.9,
						lineOnBox : 2,
						boxcolor: 0x6309BF


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
						{ fontsize: 30, borderColor: {r:255, g:0, b:0, a:0}, backgroundColor: {r:255, g:100, b:100, a:0}, textColor : "rgba(99, 8, 191 , 1.0)" } );
						bbox1.position.set(min.x ,min.y, min.z);
						scene.add( bbox1 );
				
						bbox2 = makeTextSprite(  "("+ parseInt(max.x) + "," + parseInt(min.y) + "," + parseInt(min.z) + ")", 
						{ fontsize: 30, borderColor: {r:255, g:0, b:0, a:0}, backgroundColor: {r:255, g:100, b:100, a:0}, textColor : "rgba(99, 8, 191, 1.0)" } );
						bbox2.position.set( max.x ,min.y, min.z);
						scene.add( bbox2 );

						bbox3 = makeTextSprite(  "("+ parseInt(min.x) + "," + parseInt(max.y) + "," + parseInt(min.z) + ")", 
						{ fontsize: 30, borderColor: {r:255, g:0, b:0, a:0}, backgroundColor: {r:255, g:100, b:100, a:0}, textColor : "rgba(99, 8, 191, 1.0)" } );
						bbox3.position.set( min.x ,max.y, min.z);
						scene.add( bbox3 );

						bbox4 = makeTextSprite(  "("+ parseInt(max.x) + "," + parseInt(max.y) + "," + parseInt(min.z) + ")", 
						{ fontsize: 30, borderColor: {r:255, g:0, b:0, a:0}, backgroundColor: {r:255, g:100, b:100, a:0}, textColor : "rgba(99, 8, 191, 1.0)" } );
						bbox4.position.set( max.x ,max.y, min.z);
						scene.add( bbox4 );

						bbox5 = makeTextSprite(  "("+ parseInt(min.x) + "," + parseInt(min.y) + "," + parseInt(max.z) + ")", 
						{ fontsize: 30, borderColor: {r:255, g:0, b:0, a:0}, backgroundColor: {r:255, g:100, b:100, a:0}, textColor : "rgba(99, 8, 191, 1.0)" } );
						bbox5.position.set( min.x ,min.y, max.z);
						scene.add( bbox5 );

						bbox6 = makeTextSprite(  "("+ parseInt(max.x) + "," + parseInt(min.y) + "," + parseInt(max.z) + ")", 
						{ fontsize: 30, borderColor: {r:255, g:0, b:0, a:0}, backgroundColor: {r:255, g:100, b:100, a:0}, textColor : "rgba(99, 8, 191, 1.0)" } );
						bbox6.position.set( max.x ,min.y, max.z);
						scene.add( bbox6 );

						bbox7 = makeTextSprite(  "("+ parseInt(min.x) + "," + parseInt(max.y) + "," + parseInt(max.z) + ")", 
						{ fontsize: 30, borderColor: {r:255, g:0, b:0, a:0}, backgroundColor: {r:255, g:100, b:100, a:0}, textColor : "rgba(99, 8, 191, 1.0)" } );
						bbox7.position.set( min.x ,max.y, max.z);
						scene.add( bbox7 );

					    bbox8 = makeTextSprite(  "("+ parseInt(max.x) + "," + parseInt(max.y) + "," + parseInt(max.z) + ")", 
						{ fontsize: 30, borderColor: {r:255, g:0, b:0, a:0}, backgroundColor: {r:255, g:100, b:100, a:0}, textColor : "rgba(99, 8, 191, 1.0)" } );
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
					parameters["fontsize"] : 25;
				
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
				context.fillStyle   = "rgba(" + backgroundColor.r + "," + backgroundColor.g + ","
											  + backgroundColor.b + "," + backgroundColor.a + ")";
				// border color
				context.strokeStyle = "rgba(" + borderColor.r + "," + borderColor.g + ","
											  + borderColor.b + "," + borderColor.a + ")";

				context.lineWidth = borderThickness;
				roundRect(context, borderThickness/2, borderThickness/2, textWidth + borderThickness, fontsize * 1.4 + borderThickness, 6);
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
				sprite.scale.set(200,100,1.0);
				return sprite;	
			}

			// function for drawing rounded rectangles
			function roundRect(ctx, x, y, w, h, r) 
			{
			    ctx.beginPath();
			    ctx.moveTo(x+r, y);
			    ctx.lineTo(x+w-r, y);
			    ctx.quadraticCurveTo(x+w, y, x+w, y+r);
			    ctx.lineTo(x+w, y+h-r);
			    ctx.quadraticCurveTo(x+w, y+h, x+w-r, y+h);
			    ctx.lineTo(x+r, y+h);
			    ctx.quadraticCurveTo(x, y+h, x, y+h-r);
			    ctx.lineTo(x, y+r);
			    ctx.quadraticCurveTo(x, y, x+r, y);
			    ctx.closePath();
			    ctx.fill();
				ctx.stroke();   
			}	

			// ADDED STUFF HERE
			function onMouseClick( event ){
		
				mouse.x = ( event.clientX / window.innerWidth ) * 2 - 1;
				mouse.y = - ( event.clientY / window.innerHeight ) * 2 + 1;
				console.log(mouse.x + "   " + mouse.y + " Objectssize: " + objects.length);
				raycaster.setFromCamera( mouse, camera );

				//console.log( "SCENEX:" + camera2.position.x + "SCENEY: " + camera2.position.y + "SCENEZ: " + camera2.position.z);
				//console.log( "SCENEX:" + lastPosition.x + "SCENEY: " + lastPosition.y + "SCENEZ: " + lastPosition.z);
				//console.log( "changedpositionx:" + controls.getObject().position.x + "chy: " + controls.getObject().position.y + "chz: " + controls.getObject().position.z);

				var intersects = raycaster.intersectObjects( objects );
				console.log("Intersects lengths: " + intersects.length);

				if ( intersects.length > 0 ) {
					var i = intersects[0].object.id;
					console.log("Something detected:: " + i);
					if(selected[i]){
						intersects[ 0 ].object.material.color.setHex(0xffaaa0);

						console.log("Deselected i");
						group.remove(meshArray[i]);
						selected[i] = false;
					}else{
						console.log(testCount);
						kpiColors[i] = intersects[ 0 ].object.material.color;
						intersects[ 0 ].object.material.color.setHex( 0xF234CD );

						console.log("Selected i");
						
				  		var canvas2 = document.createElement('canvas');
				  		canvas2.width = infoStrings[i].length*20;
				  		canvas2.height = 45;

						var context2 = canvas2.getContext('2d');
						context2.font = "40px Helvetica Neue";
						context2.fillStyle = "rgba(255,255,255,0.95)";

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

						mesh2.position.set(0,0,0);
						mesh2.position.set(coordsx[i],coordsy[i],coordsz[i]);
						//mesh2.rotation.x = 3*Math.PI / 2;
						//mesh2.rotation.z = Math.PI/2;
						//mesh2.rotation.y = Math.PI;

						meshArray[i]= mesh2;
						group.add( meshArray[i]);
						selected[i] = true;
					}
					scene.add(group);
					//scene.add(meshArray[i]);
				}

				/*
				// Parse all the faces
				for ( var i in intersects ) {

					intersects[ i ].face.material[ 0 ].color.setHex( Math.random() * 0xffffff | 0x80000000 );

				}
				*/
				console.log("x: " + event.x + "y: " + event.y);
			}


			function animate() {
				requestAnimationFrame( animate );
				controls.isOnObject( false );
				render();
			}

			function render()
			{
				controls.update();
				renderer.render(scene, camera);
			}

			var mousewheelevt = (/Firefox/i.test(navigator.userAgent)) ? "DOMMouseScroll" : "mousewheel" //FF doesn't recognize mousewheel as of FF3.x
			var mousewheelevt = (/Firefox/i.test(navigator.userAgent)) ? "DOMMouseScroll" : "mousewheel" //FF doesn't recognize mousewheel as of FF3.x
			$('body').bind(mousewheelevt, function(e){

				//console.log("clicked")
			    e.preventDefault();


			    var evt = window.event || e //equalize event object     
			    evt = evt.originalEvent ? evt.originalEvent : evt; //convert to originalEvent if possible               
			    var delta = evt.detail ? evt.detail*(-40) : evt.wheelDelta //check for detail first, because it is used by Opera and FF

			    dx = camera.position.x - 0;
			    dy = camera.position.y - 0;
			    dz = camera.position.z - 0;

			    normDistance = Math.sqrt(dx*dx+dy*dy+dz*dz);

			    if(delta > 0) {
			        console.log("Scroll up " + normDistance);
			        console.log("Size: " +  particleSystem.material.size)
			         	particleSystem.material.size = normDistance*.01;
			         	console.log("Size: " +  particleSystem.material.size)
			         
			    }
			    else{
			        console.log("Scroll down " + normDistance);
			        // if (particleSystem.material.size >= 1)
			        //  {
			         	particleSystem.material.size = normDistance*.01;
			         	console.log("Size: " +  particleSystem.material.size)
			         //}
			        
			    }   
			});

	



			main();

		</script>

	</body>
</html>