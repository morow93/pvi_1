<?php

  require '../vendor/autoload.php';
  require 'db.php';

  $api = new \UserApp\API('54e0a91dd2235', 'g4EkA_oqSrCw38SZXObpuw');

  $app = new \Slim\Slim([
      'debug' => true
  ]);

  $app->get('/', function () use ($app) {
      readfile('index.html');
  });

  $app->post('/posts', function () use ($app, $dbname) {

      $posts     = $dbname->posts;
      $friends   = $dbname->friends;
      $userPosts = [];
      $req       = json_decode($app->request->getBody());
      $userId    = $req->userId;
      $ids       = $friends->findOne(['UserId' => $req->userId])['Friends'];

      if (is_null($ids)) $ids = [];
      array_unshift($ids, $userId);

      foreach ($dbname->posts->find() as $post) {
          foreach ($ids as $id) {
              if ($post['UserId'] == $id) {
                  $userPosts[] = $post;
              }
          }
      }

      usort($userPosts, function($a, $b){
        if ($a['DateCreation'] == $b['DateCreation']) {
            return 0;
        }
        return ($a['DateCreation'] > $b['DateCreation']) ? -1 : 1;
      });

      $app->response->setBody(json_encode($userPosts));

  });

  $app->post('/post', function () use ($app, $dbname) {

    $posts = $dbname->posts;
      $req   = json_decode($app->request->getBody());

      $dbname->posts->insert([
        'UserId'         => $req->userId,
        'UserName'       => $req->userName,
        'Title'          => $req->title,
        'Text'           => $req->text,
        'DateCreation'   => $req->dateCreation
      ]);

  });

  $app->get('/post/:postId', function ($postId) use ($app, $dbname) {

      $posts = $dbname->posts;
      $post  = $posts->findOne([
          '_id' => new MongoId($postId)
      ]);

      $app->response->setBody(json_encode($post));

  });

  $app->delete('/post/:postId', function ($postId) use ($app, $dbname) {

    $posts = $dbname->posts;

      $dbname->posts->remove([
          '_id' => new MongoId($postId)
      ]);

  });

  $app->put('/post/:postId', function ($postId) use ($app, $dbname) {

      $posts   = $dbname->posts;
      $req     = json_decode($app->request->getBody());

      $newData = ['$set' => [
          'Title' => $req->post->Title, 'Text'  => $req->post->Text
      ]];

      $dbname->posts->update([
          '_id' => new MongoId($postId)
      ], $newData);

  });

  $app->post('/friend/:friendId', function ($friendId) use ($app, $dbname) {

      $friends = $dbname->friends;
      $req     = json_decode($app->request->getBody());
      $userId  = $req->userId;

      if (!empty($friends->findOne(['UserId' => $userId]))) {
          $newData = ['$addToSet' => ['Friends' => $friendId]];
          $friends->update(['UserId' => $userId], $newData);
      }
      else {
          $friends->save([
              'UserId' => $userId,
              'Friends' => [$friendId]
          ]);
      }

  });

  $app->put('/friend/:friendId', function ($friendId) use ($app, $dbname) {

    $friends = $dbname->friends;
    $req     = json_decode($app->request->getBody());
    $userId  = $req->userId;
    $ids     = $friends->findOne(['UserId' => $userId])['Friends'];

    for($i = 0; $i < count($ids); $i++) {
      if ($ids[$i] == $friendId) {
        $index = $i;
        break;
      }
    }

    array_splice($ids, $index, 1);
    $newData = ['$set' => ['Friends' => $ids]];
    $friends->update(['UserId' => $userId], $newData);

  });

  $app->post('/users', function () use ($app, $api, $dbname) {

      $friends = $dbname->friends;
      $users   = [];
      $userId  = json_decode($app->request->getBody())->userId;
      $ids     = $friends->findOne(['UserId' => $userId])['Friends'];

      foreach($api->user->search()->items as $items) {
          $users[] = $api->user->get([
              "user_id" => $items->user_id
          ])[0];
      }

      if (!empty($ids)) {
        foreach($users as $user) {
          foreach($ids as $id) {
            if ($user->user_id == $id) {
              $user->properties->isFriend->value = true;
            }
          }
        }
      }

      $app->response->setBody(json_encode($users));

  });

  $app->get('/user/:userId', function($userId) use ($app, $api, $dbname){

    $posts      = $dbname->posts;
    $friends    = $dbname->friends;
    $friendsIds = $friends->findOne(['UserId' => $userId])['Friends'];

    $result     = [
      'PostsCount'   => 0,
      'FriendsCount' => count($friendsIds),
      'UserName'     => '',
      'UserEmail'    => ''
    ];

    foreach ($posts->find() as $post) {
      if ($post['UserId'] == $userId) {
        ++$result['PostsCount'];
      }
    }

    $userInfo = $api->user->get([
      "user_id" => $userId
    ])[0];

    $result['UserEmail'] = $userInfo->email;
    $result['UserName']  = $userInfo->first_name;

    $app->response->setBody(json_encode($result));

  });

  $app->run();

?>
