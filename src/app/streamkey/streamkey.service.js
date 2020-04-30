(()=> {
    
    angular
        .module("insta")
        .service("streamkey.service", [ "$http", streamController])

    function streamController($http) {
        var that = this

        that.streamUrl  = "src/streamkey.service.php"
        that.codeUrl    = "src/code.service.php"

        that.stream = (login)=> {
            login.action = "stream"
            return $http({ url: that.streamUrl, method: "POST", data: login })
        }

        that.code = (login)=> {
            login.action = "code"
            return $http({ url: that.codeUrl, method: "POST", data: login })
        }
    }

}) ()