<?php

class CountableObject implements Countable {
    public function count()
    {
        return count(get_object_vars($this));
    }
}
