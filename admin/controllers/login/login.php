<?php

    class LoginController extends Controller {

        function index() {

            $this->load_library("Form");
            $this->form->init_form(ADMIN_FILE_URL, "post", null, null, "window");
            $this->form->open_table("login");
            $this->form->add_item("text", "login", "login", null, null, "required");
            $this->form->add_item("password", "password", "password", null, null, "required");
            $this->form->add_button("login");
            $this->form->close_table("login");
            $this->form->draw($ajax = false);
        }

        function logout() {
            User::logout();
        }

    }

    // -- end