<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CalcController extends Controller {
    private function calc(array& $ops, &$i) {
        $a = (double)$ops[$i - 1];
        $b = (double)$ops[$i + 1];
        switch ($ops[$i]) {
            case '*':
                $ops[$i - 1] = $a * $b;
                break;
            case '/':
                if (!$b) return false;
                $ops[$i - 1] = $a / $b;
                break;
            case '+':
                $ops[$i - 1] = $a + $b;
                break;
            case '-':
                $ops[$i - 1] = $a - $b;
                break;
        }
        array_splice($ops, $i, 2);
        $i--;
        return true;
    }

    public function handle(Request $request) {
        $request->validate([
            'operations' => ['required', 'array', 'min:2'],
            'operations.*' => ['string', 'min:1', 'max:64']
        ]);

        $ops = $request->get('operations');
        if (in_array(end($ops), ['+', '-', '*', '/'])) {
            array_pop($ops);
        }

        for ($i = 0; $i < count($ops); $i++) {
            switch ($ops[$i]) {
                case '*':
                    $this->calc($ops, $i);
                    break;
                case '/':
                    if (!$this->calc($ops, $i)) {
                        throw ValidationException::withMessages(['operations' => 'Division by zero']);
                    }
                    break;
            }
        }

        for ($i = 0; $i < count($ops); $i++) {
            switch ($ops[$i]) {
                case '+':
                case '-':
                    $this->calc($ops, $i);
                    break;
            }
        }

        return $ops[0];
    }
}
