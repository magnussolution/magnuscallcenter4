Help.load({
    'user.username': 'Usuário para o operador logar no softphone e no painel',
    'user.password': 'Senha para o operador logar no softphone e no painel',
    'user.allow_direct_call_campaign': 'Pertipe este operador ligar para qualquer número, independente se estiver logado no sistema ou não.',
    'campaign.predictive': 'Somente ative esta opção se realmente for usar o predictive',
    'campaign.call_limit': 'Deixe em zero para ser calculado automaticamente o total de chamada a ser enviado por cada operador livre. Ou coloque um valor maior que zero para enviar um total fixo de chamada',
    'campaign.call_next_try': 'Tempo em segundo a ser esperado entre cada tentativa de envio para o operadora. \n EX: Se for 30, o preditivo enviará as chamadas, e se o operadora continuar livre por 30 segundos, o sistema ira enviar mais chamada para ele.',
    'campaign.open_url_when_answer_call': 'Abrir esta URL em nota TAB quando operador receber uma chamada no Predictive. Pode passar para a URL qualquer parametro.\n. Ex: http://site.com?numero=%number%&name=%name%',
    'campaign.open_url': 'Abre esta URL em um popup no canto do painel do operador.',
    'workShift.day_start': 'Esta opção é usada para operadores se cadastrarem para trabalhar por quinzena.',
    'workShift.day_stop': 'Use Dia início e de fim para cadastrar os dias referente a quinzena',
    'team.id_trunk': 'Selecione os troncos que a equipe poderá usar. Se deixar em branco, o admin da equipe podera criar seus proprios troncos. Desde que no grupo da equipe tiver permissāo para criar troncos.',
});