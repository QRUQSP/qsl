//
// This is the main app for the qsl module
//
function qruqsp_qsl_main() {
    //
    // The panel to list the entry
    //
    this.menu = new Q.panel('entry', 'qruqsp_qsl_main', 'menu', 'mc', 'medium', 'sectioned', 'qruqsp.qsl.main.menu');
    this.menu.data = {};
    this.menu.nplist = [];
    this.menu.sections = {
        'search':{'label':'', 'type':'livesearchgrid', 'livesearchcols':1,
            'cellClasses':[''],
            'hint':'Search entry',
            'noData':'No entry found',
            },
        'entries':{'label':'Log Entry', 'type':'simplegrid', 'num_cols':1,
            'noData':'No entry',
            'addTxt':'Add Log Entry',
            'addFn':'Q.qruqsp_qsl_main.edit.open(\'Q.qruqsp_qsl_main.menu.open();\',0,null);'
            },
    }
    this.menu.liveSearchCb = function(s, i, v) {
        if( s == 'search' && v != '' ) {
            Q.api.getJSONBgCb('qruqsp.qsl.entrySearch', {'station_id':Q.curStationID, 'start_needle':v, 'limit':'25'}, function(rsp) {
                Q.qruqsp_qsl_main.menu.liveSearchShow('search',null,Q.gE(Q.qruqsp_qsl_main.menu.panelUID + '_' + s), rsp.entries);
                });
        }
    }
    this.menu.liveSearchResultValue = function(s, f, i, j, d) {
        return d.name;
    }
    this.menu.liveSearchResultRowFn = function(s, f, i, j, d) {
        return 'Q.qruqsp_qsl_main.entry.open(\'Q.qruqsp_qsl_main.menu.open();\',\'' + d.id + '\');';
    }
    this.menu.cellValue = function(s, i, j, d) {
        if( s == 'entries' ) {
            switch(j) {
                case 0: return d.name;
            }
        }
    }
    this.menu.rowFn = function(s, i, d) {
        if( s == 'entries' ) {
            return 'Q.qruqsp_qsl_main.entry.open(\'Q.qruqsp_qsl_main.menu.open();\',\'' + d.id + '\',Q.qruqsp_qsl_main.entry.nplist);';
        }
    }
    this.menu.open = function(cb) {
        Q.api.getJSONCb('qruqsp.qsl.entryList', {'station_id':Q.curStationID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                Q.api.err(rsp);
                return false;
            }
            var p = Q.qruqsp_qsl_main.menu;
            p.data = rsp;
            p.nplist = (rsp.nplist != null ? rsp.nplist : null);
            p.refresh();
            p.show(cb);
        });
    }
    this.menu.addClose('Back');

    //
    // The panel to display Log Entry
    //
    this.entry = new Q.panel('Log Entry', 'qruqsp_qsl_main', 'entry', 'mc', 'medium mediumaside', 'sectioned', 'qruqsp.qsl.main.entry');
    this.entry.data = null;
    this.entry.entry_id = 0;
    this.entry.sections = {
    }
    this.entry.open = function(cb, eid, list) {
        if( eid != null ) { this.entry_id = eid; }
        if( list != null ) { this.nplist = list; }
        Q.api.getJSONCb('qruqsp.qsl.entryGet', {'station_id':Q.curStationID, 'entry_id':this.entry_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                Q.api.err(rsp);
                return false;
            }
            var p = Q.qruqsp_qsl_main.entry;
            p.data = rsp.entry;
            p.refresh();
            p.show(cb);
        });
    }
    this.entry.addButton('edit', 'Edit', 'Q.qruqsp_qsl_main.edit.open(\'Q.qruqsp_qsl_main.entry.open();\',Q.qruqsp_qsl_main.entry.entry_id);');
    this.entry.addClose('Back');

    //
    // The panel to edit Log Entry
    //
    this.edit = new Q.panel('Log Entry', 'qruqsp_qsl_main', 'edit', 'mc', 'medium mediumaside', 'sectioned', 'qruqsp.qsl.main.edit');
    this.edit.data = null;
    this.edit.entry_id = 0;
    this.nplist = [];
    this.edit.sections = {
        'general':{'label':'', 'aside':'yes','fields':{
            'time_of_traffic_date':{'label':'Date', 'type':'date'},
            'time_of_traffic_time':{'label':'Time', 'type':'text','size':'small'},
            'frequency':{'label':'Frequency', 'type':'text'},
            'mode':{'label':'Mode', 'type':'toggle','toggles':{'0':'?','20':'LSB','30':'USB','40':'FM','10':'CW','50':'RTTY','60':'PSK','70':'JT', '100':'AM'}},
            }},
        'calls':{'label':'', 'aside':'yes','fields':{
            'from_call':{'label':'Call Sign From', 'type':'text'},
            'to_call':{'label':'Call Sign To', 'type':'text'},
            }},
        'rsts':{'label':'', 'aside':'yes','fields':{
            'from_r':{'label':'Readability From', 'type':'toggle','toggles':{'0':'?','1':'1','2':'2','3':'3','4':'4','5':'5'}},
            'from_s':{'label':'Strength From', 'type':'toggle','toggles':{'0':'?','1':'1','2':'2','3':'3','4':'4','5':'5','6':'6','7':'7','8':'8','9':'9'}},
            'from_t':{'label':'Tone From', 'type':'toggle','toggles':{'0':'?','1':'1','2':'2','3':'3','4':'4','5':'5','6':'6','7':'7','8':'8','9':'9'}},
            'to_r':{'label':'Readability To', 'type':'toggle','toggles':{'0':'?','1':'1','2':'2','3':'3','4':'4','5':'5'}},
            'to_s':{'label':'Strength To', 'type':'toggle','toggles':{'0':'?','1':'1','2':'2','3':'3','4':'4','5':'5','6':'6','7':'7','8':'8','9':'9'}},
            'to_t':{'label':'Tone To', 'type':'toggle','toggles':{'0':'?','1':'1','2':'2','3':'3','4':'4','5':'5','6':'6','7':'7','8':'8','9':'9'}},
            }},
        '_traffic':{'label':'Traffic', 'fields':{
            'traffic':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'},
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'Q.qruqsp_qsl_main.edit.save();'},
            'delete':{'label':'Delete', 
                'visible':function() {return Q.qruqsp_qsl_main.edit.entry_id > 0 ? 'yes' : 'no'; },
                'fn':'Q.qruqsp_qsl_main.edit.remove();'},
            }},
        };
    this.edit.fieldValue = function(s, i, d) { return this.data[i]; }
    this.edit.fieldHistoryArgs = function(s, i) {
        return {'method':'qruqsp.qsl.entryHistory', 'args':{'station_id':Q.curStationID, 'entry_id':this.entry_id, 'field':i}};
    }
    this.edit.open = function(cb, eid, list) {
        if( eid != null ) { this.entry_id = eid; }
        if( list != null ) { this.nplist = list; }
        Q.api.getJSONCb('qruqsp.qsl.entryGet', {'station_id':Q.curStationID, 'entry_id':this.entry_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                Q.api.err(rsp);
                return false;
            }
            var p = Q.qruqsp_qsl_main.edit;
            p.data = rsp.entry;
            p.refresh();
            p.show(cb);
        });
    }
    this.edit.save = function(cb) {
        if( cb == null ) { cb = 'Q.qruqsp_qsl_main.edit.close();'; }
        if( !this.checkForm() ) { return false; }
        if( this.entry_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                Q.api.postJSONCb('qruqsp.qsl.entryUpdate', {'station_id':Q.curStationID, 'entry_id':this.entry_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        Q.api.err(rsp);
                        return false;
                    }
                    eval(cb);
                });
            } else {
                eval(cb);
            }
        } else {
            var c = this.serializeForm('yes');
            Q.api.postJSONCb('qruqsp.qsl.entryAdd', {'station_id':Q.curStationID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    Q.api.err(rsp);
                    return false;
                }
                Q.qruqsp_qsl_main.edit.entry_id = rsp.id;
                eval(cb);
            });
        }
    }
    this.edit.remove = function() {
        if( confirm('Are you sure you want to remove entry?') ) {
            Q.api.getJSONCb('qruqsp.qsl.entryDelete', {'station_id':Q.curStationID, 'entry_id':this.entry_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    Q.api.err(rsp);
                    return false;
                }
                Q.qruqsp_qsl_main.edit.close();
            });
        }
    }
    this.edit.nextButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.entry_id) < (this.nplist.length - 1) ) {
            return 'Q.qruqsp_qsl_main.edit.save(\'Q.qruqsp_qsl_main.edit.open(null,' + this.nplist[this.nplist.indexOf('' + this.entry_id) + 1] + ');\');';
        }
        return null;
    }
    this.edit.prevButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.entry_id) > 0 ) {
            return 'Q.qruqsp_qsl_main.edit.save(\'Q.qruqsp_qsl_main.entry_id.open(null,' + this.nplist[this.nplist.indexOf('' + this.entry_id) - 1] + ');\');';
        }
        return null;
    }
    this.edit.addButton('save', 'Save', 'Q.qruqsp_qsl_main.edit.save();');
    this.edit.addClose('Cancel');
    this.edit.addButton('next', 'Next');
    this.edit.addLeftButton('prev', 'Prev');

    //
    // Start the app
    // cb - The callback to run when the user leaves the main panel in the app.
    // ap - The application prefix.
    // ag - The app arguments.
    //
    this.start = function(cb, ap, ag) {
        args = {};
        if( ag != null ) {
            args = eval(ag);
        }
        
        //
        // Create the app container
        //
        var ac = Q.createContainer(ap, 'qruqsp_qsl_main', 'yes');
        if( ac == null ) {
            alert('App Error');
            return false;
        }
        
        this.menu.open(cb);
    }
}