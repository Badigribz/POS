import axios from 'axios';

axios.defaults.baseURL = 'http://localhost:8000'; // Laravel server
axios.defaults.withCredentials = true;

export default axios;

