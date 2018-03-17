<?php

namespace Logger\Store;

interface Store {
	
    public function write($body);
}