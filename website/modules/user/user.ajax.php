<?php

    class UserAjaxModule extends Module {

        function signout(){
            User::logout();
            echo true;
        }

    }

    // -- end