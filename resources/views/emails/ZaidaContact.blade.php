<style>
    .parent {
        background-color: #f6f6f6;
        padding: 2rem;
    }

    .mail {
        font-family: Arial, Helvetica, sans-serif;
        padding: 0.5rem 1rem;
        background-color: #ff6b6b;
        border-radius: 0.5rem;
        color: white;
        max-width: 600px;
        margin: 0 auto;
    }
</style>

<div class="parent">
    <div class="mail">
        <p><b>Hola</b></p>
        <p>Se ha enviado un correo solicitando información sobre una sesión con los siguientes datos:</p>

        <p><b>Nombre:</b> {{ $name }}</p>
        <p><b>Teléfono:</b> {{ $phone }}</p>
        <p><b>Tipo de evento:</b> {{ $eventType }}</p>
        <p><b>Fecha del evento:</b> {{ $eventDate }}</p>
        <p><b>Detalles:</b> {!! $details !!}</p>
    </div>
</div>