'use strict';

angular.module('billboard')
  .config(function($stateProvider) {
    $stateProvider
      .state('home', {
        url: '/',
        templateUrl: 'app/controller/home/home.html',
        controller: 'HomeCtrl as home',
        resolve: {}
      });
  });
