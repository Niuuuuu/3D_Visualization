THREE.CustomControls = function ( object ) {
	this.camera = object;
	this.target = new THREE.Vector3( 1, 0, 0 );

	var pitchObject = new THREE.Object3D();
	pitchObject.add( camera );

	var yawObject = new THREE.Object3D();
	yawObject.position.y = 500;
	yawObject.position.x = 500;
	yawObject.position.z = 1500;
	yawObject.add( pitchObject );


	var velocity = new THREE.Vector3();

	var prevTime = performance.now();

	var moveForward;
	var moveBackward;
	var moveLeft;
	var moveRight;
	var moveUp;
	var moveDown;

	var lat = 0;
	var lon = 0;
	var phi = 0;
	var theta = 0;

	var mouseMovementX;
	var mouseMovementY;


	document.addEventListener( 'mousemove', onDocumentMouseMove, false );
	document.addEventListener( 'keydown', onDocumentKeyDown, false );
	document.addEventListener( 'keyup', onDocumentKeyUp, false );

	function onDocumentMouseMove( event ) {

		var moveX = event.movementX       ||
                    event.mozMovementX    ||
                    event.webkitMovementX ||
                    0,
        moveY = event.movementY       ||
                    event.mozMovementY    ||
                    event.webkitMovementY ||
                    0;

	    //Update the mouse movement for coming frames
	    mouseMovementX = moveX;
	    mouseMovementY = moveY;

	    console.log("Xa: " + moveX + " Ya: " + moveY);

	    camera.rotation.y -= moveX * 0.002;
		camera.rotation.x -= moveY * 0.002;

		camera.rotation.x = Math.max( - PI_2, Math.min( PI_2, camera.rotation.x ) );
	};


		function onDocumentKeyDown ( event ) {

		console.log("Pressed key");

		switch ( event.keyCode ) {

			case 38: // up
			case 87: // w
				moveForward = true;
				break;

			case 37: // left
			case 65: // a
				moveLeft = true; break;

			case 40: // down
			case 83: // s
				moveBackward = true;
				break;

			case 39: // right
			case 68: // d
				moveRight = true;
				break;

			case 82: //r
				moveUp = true;
				break;

			case 70: //f
				moveDown = true;
				break;

			case 32: // space
				if ( canJump === true ) velocity.y += 350;
				canJump = false;
				break;

		}

	};

	function onDocumentKeyUp ( event ) {

		switch( event.keyCode ) {

			case 38: // up
			case 87: // w
				moveForward = false;
				break;

			case 37: // left
			case 65: // a
				moveLeft = false;
				break;

			case 40: // down
			case 83: // s
				moveBackward = false;
				break;


			case 82: //r
				moveUp = false;
				break;

			case 70: //f
				moveDown = false;
				break;


			case 39: // right
			case 68: // d
				moveRight = false;
				break;

		}

	};

	this.update = function (delta) {

		var time = performance.now();
		var delta = ( time - prevTime ) / 1000;

		velocity.x -= velocity.x * 10.0 * delta;
		velocity.z -= velocity.z * 10.0 * delta;

		//velocity.y -= 9.8 * 100.0 * delta; // 100.0 = mass
		velocity.y -= velocity.y * 10.0 * delta;

		if ( moveForward ) velocity.z -= 4000.0 * delta;
		if ( moveBackward ) velocity.z += 4000.0 * delta;

		if ( moveLeft ) velocity.x -= 4000.0 * delta;
		if ( moveRight ) velocity.x += 4000.0 * delta;

		if (moveUp) velocity.y += 4000.0 * delta;
		if (moveDown) velocity.y -= 4000.0 * delta;

		camera.translateX( velocity.x * delta );
		camera.translateY( velocity.y * delta ); 
		camera.translateZ( velocity.z * delta );



		lon += mouseMovementX;
		lat -= mouseMovementY;

		this.lat = Math.max( - 85, Math.min( 85, lat ) );
		this.phi = ( 90 - lat ) * Math.PI / 180;
		this.theta = lon * Math.PI / 180;

		var targetPosition = this.target,
		position = this.camera.position;

		targetPosition.x = position.x + 100 * Math.sin( 1 ) * Math.cos( 1 );
		targetPosition.y = position.y + 100 * Math.cos( 1 );
		targetPosition.z = position.z + 100 * Math.sin( 1 ) * Math.sin( 1 );

		camera.lookAt( targetPosition );

		prevTime = time;

	};

	this.getObject = function () {

		return yawObject;

	};


}