<?php 
	$conn = mysqli_connect('localhost', 'root', '', 'dislike_like');

	$user_id = 2;


	if (!$conn) {
		die("Error connecting to database: " . mysqli_connect_error($conn));
		exit();
	}

	// if user clicks like or dislike button
	if (isset($_POST['action'])) {
		$post_id = $_POST['post_id'];
		$action = $_POST['action'];

		switch ($action) {
			case 'like':
				$sql = "INSERT INTO rating_info (user_id, post_id, rating_action) 
					 		VALUES ($user_id, $post_id, 'like') 
					 		ON DUPLICATE KEY UPDATE rating_action='like'";
				break;
			case 'dislike':
				$sql  = "INSERT INTO rating_info (user_id, post_id, rating_action) 
					 		VALUES ($user_id, $post_id, 'dislike') 
					 		ON DUPLICATE KEY UPDATE rating_action='dislike'";
				break;
			case 'unlike':
				$sql = "DELETE FROM rating_info 
							WHERE user_id=$user_id 
							AND post_id=$post_id";
				break;
			case 'undislike':
				$sql = "DELETE FROM rating_info 
							WHERE user_id=$user_id 
							AND post_id=$post_id";
				break;
			default:
				break;
		}

		// execute query to effect changes in the database ...
		mysqli_query($conn, $sql);
		echo getRating($post_id);
		exit(0);
	}

	$sql = "SELECT * FROM product";

	$result = mysqli_query($conn, $sql);

	// fetch all product from database
	// return them as an associative array called $product
	$product = mysqli_fetch_all($result, MYSQLI_ASSOC);

	function getLikes($id)
	{
		global $conn;

		$sql = "SELECT COUNT(*) 
					FROM rating_info 
					WHERE post_id = $id 
					 AND rating_action='like'";

		$rs = mysqli_query($conn, $sql);
		$result = mysqli_fetch_array($rs);
		return $result[0];
	}


	function getDislikes($id)
	{
		global $conn;

		$sql = "SELECT COUNT(*) 
					FROM rating_info 
					WHERE post_id = $id 
					 AND rating_action='dislike'";

		$rs = mysqli_query($conn, $sql);
		$result = mysqli_fetch_array($rs);
		return $result[0];
	}

	function getRating($id)
	{
		global $conn;
		$rating = array();

		$likes_query = "SELECT COUNT(*) 
					FROM rating_info 
					WHERE post_id = $id 
					 AND rating_action='like'";

		$dislikes_query = "SELECT COUNT(*) 
					FROM rating_info 
					WHERE post_id = $id 
					 AND rating_action='dislike'";

		$likes_rs = mysqli_query($conn, $likes_query);
		$dislikes_rs = mysqli_query($conn, $dislikes_query);

		$likes = mysqli_fetch_array($likes_rs);
		$dislikes = mysqli_fetch_array($dislikes_rs);

		$rating = [
			'likes' => $likes[0],
			'dislikes' => $dislikes[0]
		];

		return json_encode($rating);
	}



	function userLiked($post_id)
	{
		global $conn;
		global $user_id;

			$sql = "SELECT * FROM rating_info 
					WHERE user_id=$user_id AND post_id=$post_id AND rating_action='like'";

		$result = mysqli_query($conn, $sql);


		if (mysqli_num_rows($result) > 0) {
			return true;
		}else{
			return false;
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Like and Dislike system</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<style>
		.product-wrapper {
			width: 50%;
			margin: 20px auto;
			border: 1px solid #eee;
		}
		.post {
			width: 90%;
			margin: 20px auto;
			padding: 10px 5px 0px 5px;
			border: 1px solid green;
		}
		.post-info {
			margin: 10px auto 0px;
			padding: 5px;
		}
		.fa {
			font-size: 1.2em;
		}
	
		.logged_in_user {
			padding: 10px 30px 0px;
		}
	</style>
</head>
<body>
	<div class="product-wrapper">

		<?php foreach ($product as $post): ?>
			<div class="post">
				<?php echo $post['text']; ?>

				<div class="post-info">
					<i 
					  <?php if (userLiked($post['id'])): ?>
						  class="fa fa-heart like-btn"
					  <?php else: ?>
						  class="fa fa-heart-o like-btn"
					  <?php endif ?>
					  data-id="<?php echo $post['id'] ?>"></i>

					<span class="likes"><?php echo getLikes($post['id']); ?></span>

					&nbsp;&nbsp;&nbsp;&nbsp;
				</div>
			</div>
		<?php endforeach ?>

	</div>
</body>
</html>
<script>

	$(document).ready(function(){
		// if the user clicks on the like button ...
		$('.like-btn').on('click', function(){
			var post_id = $(this).data('id');
			$clicked_btn = $(this);

			if ($clicked_btn.hasClass('fa-heart-o')) {
				action = 'like';
			} else if($clicked_btn.hasClass('fa-heart')){
				action = 'unlike';
			}


			$.ajax({
				url: 'index.php',
				type: 'post',
				data: {
					'action': action,
					'post_id': post_id
				},
				success: function(data){
					res = JSON.parse(data);

					if (action == "like") {
						$clicked_btn.removeClass('fa-heart-o');
						$clicked_btn.addClass('fa-heart');
					} else if(action == "unlike") {
						$clicked_btn.removeClass('fa-heart');
						$clicked_btn.addClass('fa-heart-o');
					}

					$clicked_btn.siblings('span.likes').text(res.likes);
					$clicked_btn.siblings('span.dislikes').text(res.dislikes);

					$clicked_btn.siblings('i.fa-thumbs-down').removeClass('fa-thumbs-down').addClass('fa-thumbs-o-down');
				}
			});		

		});

		// if the user clicks on the dislike button ...
		

	});

</script>