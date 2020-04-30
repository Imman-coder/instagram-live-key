(()=> {
    angular
        .module("insta")
        .component("header", {
            templateUrl: "src/app/header/header.html",
            controller: [ "$scope", HeaderController]
        })
    
    function HeaderController($scope) {
       
    }

}) ()