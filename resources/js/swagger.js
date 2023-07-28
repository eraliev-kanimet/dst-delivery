import SwaggerUI from 'swagger-ui'
import 'swagger-ui/dist/swagger-ui.css';

SwaggerUI({
    dom_id: '#swagger-root',
    url: import.meta.env.VITE_APP_URL + '/docs.yaml',
});
