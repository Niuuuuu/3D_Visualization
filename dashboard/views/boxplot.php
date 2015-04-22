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
		<script src="../js/Tween.js"></script>
		<script src="../js/Projector.js"></script>
		<script src="../js/effects/TrackballControls.js"></script>
		<script src="../js/effects/PointerLockControls.js"></script>
		<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>


		<script>
			//Required to setup the scene
			var scene;
			var camera;
			var renderer;
			var controls;
			var group = new THREE.Group();

			//Used for dat
			var plot;
			var particleSystem;
			var mouse = { x: 0, y: 0 };
			var projector, ray;
			var geometry;
			var objectMetaData = [];
			var objects = [];
			var raycaster = new THREE.Raycaster();
			var mouse = new THREE.Vector2();
			var projector = new THREE.Projector();

			document.addEventListener( 'keypress', onDocumentKeyPress, false );


			var bounds =
			{
			    'maxx' : 2000,
			    'minx' : -2000,
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

				projector = new THREE.Projector();
					ray = new THREE.Ray();

				//Place the camera in the correct place
				camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 1, 10000000);
			    camera.position.z = 200;
			    camera.position.x = 0;
			    camera.position.y = 0;
			    camera.lookAt(scene.position);
			    scene.add(camera);
				// soft white light
			    var light = new THREE.HemisphereLight( 0xffffff, 0xffffff, 1 ); 
				scene.add( light );

			    controls = new THREE.TrackballControls(camera);
				
				//Plot will hold out "grid"
				plot = new THREE.Object3D();
				scene.add(plot);

				scene.fog = null;

				xAxisGeo.vertices.push(new THREE.Vector3(bounds.minx, 0, 0), new THREE.Vector3(bounds.maxx, 0, 0));
				yAxisGeo.vertices.push(new THREE.Vector3(0, bounds.miny, 0), new THREE.Vector3(0, bounds.maxy, 0));
				zAxisGeo.vertices.push(new THREE.Vector3(0, 0, bounds.minz), new THREE.Vector3(0, 0, bounds.maxz));

					//Front Face
					var lineGeoMat = new THREE.LineBasicMaterial({color: 0xffffff, lineWidth: 1});
					
					//Bottom Line
					var bottomLineGeo = new THREE.Geometry();
					bottomLineGeo.vertices.push(new THREE.Vector3(bounds.minx, bounds.miny, bounds.maxz), new THREE.Vector3(bounds.maxx, bounds.miny, bounds.maxz));
					var bottomLine = new THREE.Line(bottomLineGeo, lineGeoMat);
					bottomLine.type = THREE.Lines;
					plot.add(bottomLine);

					//Bottom tick marks
					for(i = bounds.minx; i <= bounds.maxx; i += 25)
					{
						var topLineGeo = new THREE.Geometry();
						if(i % 100 == 0)
						{
							topLineGeo.vertices.push(new THREE.Vector3(i, bounds.miny, bounds.maxz), new THREE.Vector3(i, bounds.miny, bounds.maxz - 100));
							addFlatText(i, 30, i, bounds.miny, bounds.maxz + 20, (-Math.PI / 2), 0, 0);

						}
						else
						{
							topLineGeo.vertices.push(new THREE.Vector3(i, bounds.miny, bounds.maxz), new THREE.Vector3(i, bounds.miny, bounds.maxz - 25));
						}
						var topLine = new THREE.Line(topLineGeo, lineGeoMat);
						topLine.type = THREE.Lines;
						plot.add(topLine);
					}

					//TopLine
					var topLineGeo = new THREE.Geometry();
					topLineGeo.vertices.push(new THREE.Vector3(bounds.minx, bounds.maxy, bounds.maxz), new THREE.Vector3(bounds.maxx, bounds.maxy, bounds.maxz));
					var topLine = new THREE.Line(topLineGeo, lineGeoMat);
					topLine.type = THREE.Lines;
					plot.add(topLine);

					//Left
					var leftLineGeo = new THREE.Geometry();
					leftLineGeo.vertices.push(new THREE.Vector3(bounds.minx, bounds.miny, bounds.maxz), new THREE.Vector3(bounds.minx, bounds.maxy, bounds.maxz));
					var leftLine = new THREE.Line(leftLineGeo, lineGeoMat);
					leftLine.type = THREE.Lines;
					plot.add(leftLine);

					//right
					var rightLineGeo = new THREE.Geometry();
					rightLineGeo.vertices.push(new THREE.Vector3(bounds.maxx, bounds.miny, bounds.maxz), new THREE.Vector3(bounds.maxx, bounds.maxy, bounds.maxz));
					var rightLine = new THREE.Line(rightLineGeo, lineGeoMat);
					rightLine.type = THREE.Lines;
					plot.add(rightLine);

					//Right tick marks
					for(i = bounds.miny; i <= bounds.maxy; i += 25)
					{
						var rightLineGeo = new THREE.Geometry();
						if(i % 100 == 0)
						{
							rightLineGeo.vertices.push(new THREE.Vector3(bounds.maxx, i, bounds.maxz), new THREE.Vector3(bounds.maxx - 100, i, bounds.maxz));
						}
						else
						{
							rightLineGeo.vertices.push(new THREE.Vector3(bounds.maxx, i, bounds.maxz), new THREE.Vector3(bounds.maxx - 25, i, bounds.maxz));
						}
						var rightLine = new THREE.Line(rightLineGeo, lineGeoMat);
						rightLine.type = THREE.Lines;
						plot.add(rightLine);
					}


					//Back Face
					var backLineGeoMat = new THREE.LineBasicMaterial({color: 0xffffff, lineWidth: 1});
					
					//Bottom Line
					var backBottomLineGeo = new THREE.Geometry();
					backBottomLineGeo.vertices.push(new THREE.Vector3(bounds.minx, bounds.miny, bounds.minz), new THREE.Vector3(bounds.maxx, bounds.miny, bounds.minz));
					var backBottomLine = new THREE.Line(backBottomLineGeo, backLineGeoMat);
					backBottomLine.type = THREE.Lines;
					plot.add(backBottomLine);

					//TopLine
					var topLineGeo = new THREE.Geometry();
					topLineGeo.vertices.push(new THREE.Vector3(bounds.minx, bounds.maxy, bounds.minz), new THREE.Vector3(bounds.maxx, bounds.maxy, bounds.minz));
					var topLine = new THREE.Line(topLineGeo, lineGeoMat);
					topLine.type = THREE.Lines;
					plot.add(topLine);

					//Left
					var leftLineGeo = new THREE.Geometry();
					leftLineGeo.vertices.push(new THREE.Vector3(bounds.minx, bounds.miny, bounds.minz), new THREE.Vector3(bounds.minx, bounds.maxy, bounds.minz));
					var leftLine = new THREE.Line(leftLineGeo, lineGeoMat);
					leftLine.type = THREE.Lines;
					plot.add(leftLine);

					//right
					var rightLineGeo = new THREE.Geometry();
					rightLineGeo.vertices.push(new THREE.Vector3(bounds.maxx, bounds.miny, bounds.minz), new THREE.Vector3(bounds.maxx, bounds.maxy, bounds.minz));
					var rightLine = new THREE.Line(rightLineGeo, lineGeoMat);
					rightLine.type = THREE.Lines;
					plot.add(rightLine);

					//Connecting Lines
					var lineGeoMat = new THREE.LineBasicMaterial({color: 0xffffff, lineWidth: 1});
					
					//Top Left
					var bottomLineGeo = new THREE.Geometry();
					bottomLineGeo.vertices.push(new THREE.Vector3(bounds.minx, bounds.maxy, bounds.minz), new THREE.Vector3(bounds.minx, bounds.maxy, bounds.maxz));
					var bottomLine = new THREE.Line(bottomLineGeo, lineGeoMat);
					bottomLine.type = THREE.Lines;
					plot.add(bottomLine);

					//Top Right
					var topLineGeo = new THREE.Geometry();
					topLineGeo.vertices.push(new THREE.Vector3(bounds.maxx, bounds.maxy, bounds.minz), new THREE.Vector3(bounds.maxx, bounds.maxy, bounds.maxz));
					var topLine = new THREE.Line(topLineGeo, lineGeoMat);
					topLine.type = THREE.Lines;
					plot.add(topLine);

					//Bottom Left
					var leftLineGeo = new THREE.Geometry();
					leftLineGeo.vertices.push(new THREE.Vector3(bounds.minx, bounds.miny, bounds.minz), new THREE.Vector3(bounds.minx, bounds.miny, bounds.maxz));
					var leftLine = new THREE.Line(leftLineGeo, lineGeoMat);
					leftLine.type = THREE.Lines;
					plot.add(leftLine);

					//Left tick marks
					for(i = bounds.minz; i <= bounds.maxz; i += 25)
					{
						var leftLineGeo = new THREE.Geometry();
						if(i % 100 == 0)
						{
							leftLineGeo.vertices.push(new THREE.Vector3(bounds.minx, bounds.miny, i), new THREE.Vector3(bounds.minx + 100, bounds.miny, i));
						}
						else
						{
							leftLineGeo.vertices.push(new THREE.Vector3(bounds.minx, bounds.miny, i), new THREE.Vector3(bounds.minx + 25, bounds.miny, i));
						}
						var leftLine = new THREE.Line(leftLineGeo, lineGeoMat);
						leftLine.type = THREE.Lines;
						plot.add(leftLine);
					}

					//Bottom Right
					var rightLineGeo = new THREE.Geometry();
					rightLineGeo.vertices.push(new THREE.Vector3(bounds.maxx, bounds.miny, bounds.minz), new THREE.Vector3(bounds.maxx, bounds.miny, bounds.maxz));
					var rightLine = new THREE.Line(rightLineGeo, lineGeoMat);
					rightLine.type = THREE.Lines;
					plot.add(rightLine);


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


				
					


				geometry = new THREE.Geometry();
				var  idcount = 0;
				$.post( "api/threeAxisData", '<?php echo $data; ?>')
				.done(function( data ) {
					data = JSON.parse(data);
					$.each(data, function(siteName, coords)
					{
						var vertex = new THREE.Vector3( coords.x, coords.y, coords.z );
						vertex.id = idcount;
						vertex.siteName = siteName;
						geometry.vertices.push(vertex);

						var boxgeometry = new THREE.BoxGeometry( 1, 1, 1 );
						
						var object = new THREE.Mesh( boxgeometry, new THREE.MeshBasicMaterial());
						object.position.x = coords.x;
						object.position.y = coords.y;
						object.position.z = coords.z;

						//objectsData[coords.x][coords.y][coords.z].push(idcount);
						idcount++;

					});
					var sprite = THREE.ImageUtils.loadTexture("../disc.png");
					var material = new THREE.ParticleBasicMaterial({size: 1, depthTest: false, transparent: true, color :0xffaaa0, map: sprite});
					particleSystem = new THREE.ParticleSystem(geometry, material);
					particleSystem.dynamic = true;
					plot.add(THREE.ParticleSystem( geometry, material));
					group.add(particleSystem);
				});

				

				//labels facing camera
				var spritey = makeTextSprite( "<?php echo $labels['xAxis']; ?>+ ", 
						{ fontsize: 25, borderColor: {r:255, g:0, b:0, a:1.0}, backgroundColor: {r:255, g:100, b:100, a:0.5} } );
						spritey.position.set(0,bounds.miny,bounds.maxz+20);
						scene.add( spritey );

				addFlatText("<?php echo $labels['xAxis']; ?>+ ", 50, 0, bounds.miny, bounds.maxz + 200, (-Math.PI / 2), 0, 0);

				var spritey = makeTextSprite( "<?php echo $labels['yAxis']; ?>+ ", 
						{ fontsize: 25 , borderColor: {r:0, g:0, b:255, a:1.0}, backgroundColor: {r:100, g:100, b:255, a:0.5} } );
						spritey.position.set(10,500,10);
						scene.add( spritey );

				addFlatText("<?php echo $labels['yAxis']; ?>+ ", 50, bounds.maxx + 100, (bounds.miny + bounds.maxy)/2, bounds.maxz, (Math.PI), (Math.PI), (-3*Math.PI / 2));

				var spritey = makeTextSprite( "<?php echo $labels['zAxis']; ?>+ ", 
						{ fontsize: 25 , borderColor: {r:0, g:255, b:0, a:1.0}, backgroundColor: {r:100, g:255, b:100, a:0.8} } );
						spritey.position.set(10,10,500);
						scene.add( spritey );

				
				addFlatText("<?php echo $labels['zAxis']; ?>+ ", 50, bounds.minx - 100, bounds.miny, (bounds.minz + bounds.maxz)/2, -Math.PI/2, 0, Math.PI/2);


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

			function addFlatText(textLabel, fontSize, x, y, z, rotationx, rotationy, rotationz)
			{
				var textCanvas = document.createElement('canvas');
			  	textCanvas.width = bounds.maxx / 2 + 100;
				textCanvas.height = 500;
				var textContext = textCanvas.getContext('2d');
				textContext.font = fontSize+"pt Helvetica";
				textContext.textAlign = "center";
				textContext.textBaseline = "middle";
				textContext.fillStyle = "rgba(255, 255, 255,1.0)";
				var metrics = textContext.measureText( textLabel );

				var textWidth = metrics.width;

				textContext.fillText(textLabel, textCanvas.width/2, textCanvas.height / 2);

				// canvas contents will be used for a texture
				var textTexture = new THREE.Texture(textCanvas)
				textTexture.needsUpdate = true;

				var textMaterial = new THREE.MeshBasicMaterial( {map: textTexture, side:THREE.DoubleSide } );
				textMaterial.transparent = true;

				var textMesh = new THREE.Mesh(
					new THREE.PlaneBufferGeometry(textCanvas.width, textCanvas.height),
					textMaterial
				);
				textMesh.position.set(x, y, z);
				textMesh.rotation.x = rotationx;
				textMesh.rotation.y = rotationy;
				textMesh.rotation.z = rotationz;
				plot.add( textMesh );
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
				context.fillStyle = "rgba(255, 255, 255, 1.0)";

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
			function animate() {
				requestAnimationFrame( animate );
				controls.isOnObject( false );
				
				render();
			}

			function render()
			{
				TWEEN.update();
				controls.update();
				renderer.render(scene, camera);
			}

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
	
			//document.addEventListener( 'mousemove', mousemove, false );
			document.addEventListener( 'click', mousemove, false );

			function mousemove( event ) {

                event.preventDefault();

                var vector = new THREE.Vector3( ( event.clientX / window.innerWidth ) * 2 - 1, - ( event.clientY / window.innerHeight ) * 2 + 1, 0.5 );

                mouse.x = ( event.clientX / renderer.domElement.width ) * 2 - 1;
				mouse.y = - ( event.clientY / renderer.domElement.height ) * 2 + 1;

				raycaster.setFromCamera( mouse, camera );
				raycaster.params.PointCloud.threshold = 5;

				var intersects = raycaster.intersectObjects( [particleSystem], true );


                if ( intersects.length > 0 ) {
                	//console.log(intersects);
                	for (var i = 0, len = intersects.length; i < len; i++) {
                		//console.log(intersects[i]);
                		var particle = particleSystem.geometry.vertices[intersects[i].index];
                		console.log(particle.siteName);

						addFlatText(particle.siteName, 15, particle.x, particle.y, particle.z, 0, 0, 0);

					}
                    

                }

            }

            function onDocumentKeyPress( event ) {

				var keyCode = event.which;

				if(keyCode == 97)
				{
					TWEEN.removeAll();
					var rad90 = Math.PI * .01;
					new TWEEN.Tween( plot.rotation ).to( {y: plot.rotation.y+rad90}, 100 ).easing( TWEEN.Easing.Quadratic.In ).start(); 
				}
				else if(keyCode == 100)
				{
					TWEEN.removeAll();
					var rad90 = Math.PI * .01;
					new TWEEN.Tween( plot.rotation ).to( {y: plot.rotation.y-rad90}, 100 ).easing( TWEEN.Easing.Quadratic.In ).start(); 
				}
				else if(keyCode == 119)
				{
					TWEEN.removeAll();
					var rad90 = Math.PI * .01;
					new TWEEN.Tween( plot.rotation ).to( {x: plot.rotation.x+rad90}, 100 ).easing( TWEEN.Easing.Quadratic.In ).start(); 
				}
				else if(keyCode == 115)
				{
					TWEEN.removeAll();
					var rad90 = Math.PI * .01;
					new TWEEN.Tween( plot.rotation ).to( {x: plot.rotation.x-rad90}, 100 ).easing( TWEEN.Easing.Quadratic.In ).start(); 
				}
				else
				{
					console.log("This is" + keyCode);
				}
				


			}

            var canvas2 = document.createElement('canvas');
				  		

            main();


			

		</script>

	</body>
</html>
