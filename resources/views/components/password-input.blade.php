@props(['name', 'id' => null, 'autocomplete' => 'current-password'])

@php($id ??= $name)

<div class="input-group" x-data="{ show: false }">
    <input id="{{ $id }}" name="{{ $name }}" :type="show ? 'text' : 'password'" type="password"
           autocomplete="{{ $autocomplete }}" required {{ $attributes }}>
    <button type="button" class="btn-link" @click="show = !show"
            :aria-label="show ? 'Ascunde parola' : 'Arată parola'"
            x-text="show ? 'Ascunde' : 'Arată'">Arată</button>
</div>
