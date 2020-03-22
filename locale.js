(function() {
    var _constants = {};
    window.Locale = {
        load: function(constants) {
            _constants = constants;
        }
    };
    /**
     * Recebe uma string. Se achar tradução, retorna. 
     * Caso contrário, retorna a própria string.
     * 
     *      _('Administração') em pt-BR retorna 'Administração'
     *      mas em en-US vai achar a tradução e retornar 'Administration'
     * 
     */
    window.t = function(string) {
        return _constants[string] || string;
    }
}());
(function() {
    var _constants = {};
    window.Help = {
        load: function(constants) {
            _constants = constants;
        }
    };
    window.h = function(string) {
        return _constants[string] ? '<a href="#" alt="' + t(_constants[string] || string) + '" class="tooltipHelp" > <img src="./resources/images/help.jpg" /> </a>&nbsp;&nbsp;&nbsp;' : '';
    }
}());