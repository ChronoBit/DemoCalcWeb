<template>
    <Body class="bg-slate-900 h-screen text-white flex flex-col"/>
    <div class="demo-title">
        Calculator Demo
    </div>
    <div class="flex justify-center items-center h-screen">
        <div class="calc-bg">
            <input class="calc" type="text" readonly v-model="result"/>
            <div class="calc-btns">
                <button @click="onNumber">7</button>
                <button @click="onNumber">8</button>
                <button @click="onNumber">9</button>
                <button @click="onOperator">/</button>
            </div>
            <div class="calc-btns">
                <button @click="onNumber">4</button>
                <button @click="onNumber">5</button>
                <button @click="onNumber">6</button>
                <button @click="onOperator">*</button>
            </div>
            <div class="calc-btns">
                <button @click="onNumber">1</button>
                <button @click="onNumber">2</button>
                <button @click="onNumber">3</button>
                <button @click="onOperator">-</button>
            </div>
            <div class="calc-btns">
                <button @click="onNumber">0</button>
                <button @click="onNumber">.</button>
                <button :disabled="result.length == 0 || isSend" @click="onResult">=</button>
                <button @click="onOperator">+</button>
            </div>
            <div class="calc-btns">
                <button @click="clear">C</button>
                <button @click="del">DEL</button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import {isDigit} from "json5/lib/util";

useSeoMeta({
    title: 'Calculator - Demo Project'
});

const isSend = ref(false);
const operations = ref([]);
const result = computed(() => operations.value.length ? operations.value.join('') : '0');
const onNumber = (e: MouseEvent) => {
    const target = e.target as HTMLElement;
    number(target.textContent);
}
const onOperator = (e: MouseEvent) => {
    const target = e.target as HTMLElement;
    operator(target.textContent);
}

const onKey = (e: KeyboardEvent) => {
    switch(e.key) {
        case 'Delete':
        case 'Backspace':
            del();
            break;
        case 'Enter':
        case '=':
            onResult();
            break;
        case '1':
        case '2':
        case '3':
        case '4':
        case '5':
        case '6':
        case '7':
        case '8':
        case '9':
        case '0':
        case '.':
            number(e.key);
            break;
        case '+':
        case '-':
        case '*':
        case '/':
            operator(e.key);
            break;
    }
}

onMounted(() => document.addEventListener("keydown", onKey));
onUnmounted(() => document.removeEventListener("keydown", onKey));

const onResult = async () => {
    const ops = operations.value;
    if (isSend.value || ops.length < 2) return;

    let last = ops[ops.length - 1];
    if (last[last.length - 1] == '.') {
        ops[ops.length - 1] = last.substr(0, last.length - 1);
    }

    let body = {
        operations: [...ops]
    };
    if (body.operations[0][0] == '-') {
        body.operations.unshift('0');
    }

    isSend.value = true;
    // noinspection TypeScriptValidateTypes
    const { data: result, error: er } = await useFetch('http://localhost/api/calculate', {
        method: 'POST',
        body: body,
    })
    if (!er.value) {
        let r = parseFloat(parseFloat(result.value).toFixed(2));
        if (r < 0) {
            operations.value = ['-', (r * -1).toString()];
        } else {
            operations.value = [r.toString()];
        }
    }
    isSend.value = false;
}

const clear = () => {
    if (isSend.value) return;
    operations.value = [];
}

const del = () => {
    if (isSend.value) return;
    const ops = operations.value;
    if (ops.length == 0) return;
    let last = ops[ops.length - 1];
    last = last.substr(0, last.length - 1);
    if (last.length == 0) {
        ops.pop();
    } else {
        ops[ops.length - 1] = last;
    }
}

const number = (num) => {
    if (isSend.value) return;
    if (!isDigit(num) && num != '.') return;
    const ops = operations.value;
    if (ops.length == 0) {
        if (num == 0) return;
        if (num == '.') {
            ops.push('0.');
            return;
        } else {
            ops.push(num);
            return;
        }
    } else {
        let last = ops[ops.length - 1];
        if (['+', '-', '*', '/'].includes(last)) {
            if (num == 0) return;
            if (num == '.') {
                ops.push('0.');
                return;
            } else {
                ops.push(num);
                return;
            }
        } else {
            if (num == '.' && last.includes('.')) return;
            if (num == '0' && last == '0') return;
            ops[ops.length - 1] += num;
        }
    }
}

const operator = (op) => {
    if (isSend.value) return;
    if (!['+', '-', '*', '/'].includes(op)) return;
    const ops = operations.value;
    if (ops.length == 0) {
        if (op == '-') ops.push('-');
        return;
    }
    let last = ops[ops.length - 1];
    if (last[last.length - 1] == '.') {
        ops[ops.length - 1] = last.substr(0, last.length - 1);
    } else if (['+', '-', '*', '/'].includes(last)) {
        return;
    }
    ops.push(op);
}
</script>

<style lang="scss">
body {
    @apply font-[Nunito];
}
.demo-title {
    @apply absolute w-full backdrop-blur transition-colors duration-500
    border-b border-slate-700 bg-slate-800/75 p-2 text-slate-500;
}
input[class=calc] {
    @apply bg-gray-900 border border-gray-700 text-right p-3 rounded-md;
    &:focus {
        @apply border-indigo-400 outline-none;
    }
}
button {
    @apply bg-slate-900 border border-gray-700 p-4 rounded-md;
    &:not(:disabled) {
        &:hover {
            @apply bg-indigo-800;
        }
        &:focus {
            @apply text-indigo-400 border-indigo-400;
        }
        &:active {
            @apply bg-indigo-700;
        }
    }
    &:disabled {
        @apply bg-gray-800 border-gray-900 text-gray-600;
    }
}
.calc-bg {
    @apply flex flex-col gap-2 p-4 bg-slate-800 rounded-lg
    border border-gray-700 text-2xl;
}
.calc-btns {
    @apply flex justify-between gap-2;
    > button {
        @apply flex-1
    }
}
</style>