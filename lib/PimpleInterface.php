<?php

interface PimpleInterface extends ArrayAccess
{
    function share(Closure $value);

    function protect($value);

    function raw($value);

    function extend($value, Closure $decorator);

    function keys();
}
