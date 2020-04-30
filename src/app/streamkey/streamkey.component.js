(()=> {

    angular
        .module("insta")
        .component("streamkey", {
            templateUrl: "src/app/streamkey/streamkey.html",
            controller: [ "$scope", "$timeout", "streamkey.service", StreamKeyController ],    
        })

    function StreamKeyController($scope, $timeput, service) {
    
        $scope.loaded = ()=> {
            $timeput(()=> { M.updateTextFields() },10)
        }

        $scope.control = {
            bar: false,
            code: false,
            form: true,
        }

        $scope.login = {
            user: "lucascosta4590",
            password: "lucasfrct@2020",
        }
        
        $scope.code = ""

        $scope.stream = {
            server: "",
            key: ""
        }
        
        $scope.copy = (id)=> {
            
            el = angular.element(document.querySelector("#"+id));
            el[0].select()
            el[0].setSelectionRange(0, 99999);
            document.execCommand("copy");  
            
            M.toast({ html: 'Copiado com sucesso' })
        }

        $scope.getstream = (login)=> {

            $scope.control.bar = true
            
            if (!$scope.control.code) {
                
                login.id = IdCreate(login.user);
                
                eventCode(login.id)
                
                service.stream(login).then((data)=> {
                    console.log("STREAM RESPONSE", data.data)
                    $scope.control.bar = false
                    $scope.loaded()
                })

            }

            if ($scope.control.code) { sendCode(login) }
            
        }

        function eventCode(id) {
            EventCode = new EventSource("src/observer.code.php?id="+id)

            EventCode.addEventListener("code", function(event) {
                
                console.log("EVENT CODE TRIGGER: ", event.data)
    
                if ((event.data !== "{}") && compareLogin(String(event.data))) {

                    //console.log("EVENT CODE TRIGGER: ", event.data)
                                            
                    $scope.login = JSON.parse(event.data)

                    if ($scope.login.error.length > 0) {
                        M.toast({ html: 'Favor inserir usuário e senha válidos' })
                    }
                        
                    if ($scope.login.require == true) {

                        $scope.control.code = true
                        $scope.control.bar = false
                    }

                    if ($scope.login.key) {
                        
                        $scope.control.form = false
                        $scope.control.bar = false

                        $scope.stream.server = $scope.login.server
                        $scope.stream.key = $scope.login.key
                        
                        $scope.loaded()

                    }
                    
                    $scope.$apply()
                }
            
            })
        }

        function compareLogin(str) {
            return (String(JSON.stringify(angular.copy($scope.login))) != str)
        } 

        function sendCode(login) {
            login.code = $scope.code
            service.code(login).then((code)=> {
                console.log("RESPONSE SEND CODE  :", code.data)
                $scope.loaded()
            })
        }

        function IdCreate ( user ) {
            return String(String(Math.floor(Date.now() / 1000))+"-"+user);
        }

    }

}) ()