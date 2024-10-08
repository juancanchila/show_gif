jQuery(document).ready(function ($) {
    // Variable para almacenar los datos de los videos
    let videoData = {};

    // Hacer una petición GET a nuestro endpoint personalizado
    $.ajax({
        url: '/web/wp-json/show-gif/v1/lista-videos/',
        type: 'GET',
        success: function (data) {
            console.log("Lista de Videos:", data);

            // Verifica si data tiene contenido
            if (data && data.length > 0) {
                // Crear un objeto para búsqueda rápida
                videoData = data.reduce(function (acc, video) {
                    acc[video.video_name] = video.video_path;
                    return acc;
                }, {});

                console.log("Video Data Object:", videoData);

                // Llamar a la función para manejar los eventos de <h1>
                handleH1Events();
            } else {
                console.warn("No se encontraron videos.");
            }
        },
        error: function (xhr, status, error) {
            console.error("Error al obtener la lista de videos: ", status, error);
        }
    });

    // Función que maneja el evento de mouseover y click
    function handleH1Events() {
        // Selecciona todos los elementos <h1> en la página
        const anchorElements = document.querySelectorAll('a');

        // Añade eventos a cada elemento <a>
        anchorElements.forEach(anchor => {
            // Evento de mouseover
            anchor.addEventListener('mouseover', function() {
                const videoName = this.textContent.trim();
                console.log(videoName );
                updateGif(videoName);
            });

           // Evento de click
           anchor.addEventListener('mouseover', function() {
                const videoName = this.textContent.trim();
                updateGif(videoName);
            });
        });
    }

    // Función para actualizar el GIF basado en el título
    function updateGif(videoName) {
        const gifElement = document.getElementById('dynamic-gif');
        const decodedVideoName = decodeURIComponent(videoName);

        const videoPath = videoData[decodedVideoName];

        if (videoPath) {
            // Usa la URL completa directamente
            const fullUrl = `https://epacartagena.gov.co/${videoPath}`;
            gifElement.src = fullUrl;
            gifElement.style.display = 'block'; // Asegura que el GIF se muestre
            console.log('GIF actualizado a:', fullUrl);
        } else {
            // Oculta el GIF si no hay coincidencia
            gifElement.src = '';
            gifElement.style.display = 'none';
            console.log('No se encontró ruta para el título:', videoName);
        }
    }
});
