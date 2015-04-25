'use strict';

angular.module('billboard')
  .config(function($stateProvider) {
    $stateProvider
      .state('login', {
        url: '/login',
        templateUrl: 'app/controller/login/login.html',
        data: {
          login: true
        }
      });
  });
