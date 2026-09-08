<?php

use function Pest\Laravel\get;

describe("home page works" ,function(){
    it("open succefully " , function (){
        get("/")->assertStatus(200);
    });
    it("open login page " , function (){
        $page = get("/");
        $page->click("login");
        
    });
});