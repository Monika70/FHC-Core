import StudierendenantragStatus from './Status.js';
import Phrasen from '../../mixins/Phrasen.js';

export default {
	components: {
		StudierendenantragStatus
	},
	mixins: [Phrasen],
	props: {
		antragId: Number,
		initialStatusCode: String,
		initialStatusMsg: String,
		disabled: Boolean
	},
	data() {
		return {
			lvs: [],
			isloading: false,
			statusCode: '',
			statusMsg: ''
		};
	},
	computed: {
		lvs1() {
			return this.lvs[Object.keys(this.lvs).filter(key => key.substr(0, 1) == 1)] || [];
		},
		lvs2() {
			return this.lvs[Object.keys(this.lvs).filter(key => key.substr(0, 1) == 2)] || [];
		},
		lvs1sem(){
			return (Object.keys(this.lvs).filter(key => key.substr(0, 1) == 1).pop() || "1").substr(1);
		},
		lvs2sem(){
			return (Object.keys(this.lvs).filter(key => key.substr(0, 1) == 2).pop() || "2").substr(1);
		},
		statusSeverity() {
			switch (this.statusCode) {
				case 0: return 'danger';
				default: return 'info';
			}
		}
	},
	methods: {
		save() {
			this.isloading = true;
			const forbiddenLvs = this.lvs1.filter(lv => lv.antrag_zugelassen && !lv._children).map(lv => ({
				studierendenantrag_id: this.antragId,
				lehrveranstaltung_id: lv.lehrveranstaltung_id,
				zugelassen: 0,
				anmerkung: lv.antrag_anmerkung || "",
				studiensemester_kurzbz: this.lvs1sem
			}));
			const mandatoryLvs = this.lvs2.filter(lv => !lv._children).map(lv => ({
				studierendenantrag_id: this.antragId,
				lehrveranstaltung_id: lv.lehrveranstaltung_id,
				zugelassen:lv.antrag_zugelassen ? 1 : 2,
				anmerkung: lv.antrag_anmerkung || "",
				studiensemester_kurzbz: this.lvs2sem
			}));
			axios.post(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Antrag/Wiederholung/saveLvs/', {forbiddenLvs, mandatoryLvs})
				.then(response => {
					if(!response.data.error) {
						this.addAlert('Speichern erfolgreich', 'alert-success');
						this.statusCode = response.data.retval[0].studierendenantrag_statustyp_kurzbz;
						this.statusMsg = response.data.retval[0].typ;
					} else {
						this.addAlert(response.data.retval, 'alert-danger');
						this.statusCode = 0;
						this.statusMsg = 'Error';
					}
					this.isloading = false;
				}).catch(error => {
					this.addAlert(error.message, 'alert-danger');
					this.statusCode = 0;
					this.statusMsg = 'Error';
					this.isloading = false;
				}).finally(() => {
					window.scrollTo(0, 0);
				});
		},
		addAlert(text, type) {
			const para = document.createElement("p");
			para.innerText = text;
			para.className = "alert " + type + " alert-dismissible fade show";
			const btn =  document.createElement("button");
			btn.className = "btn-close";
			btn.type = "button";
			btn.setAttribute("aria-label", "Close");
			btn.setAttribute("data-bs-dismiss", "alert");
			para.appendChild(btn);

			this.$refs.alertbox.appendChild(para);
		}
	},
	created() {
		this.statusCode = this.initialStatusCode;
		this.statusMsg = this.initialStatusMsg;
	},
	mounted() {
		axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Antrag/Wiederholung/getLvs/' + this.antragId).then(
			result => {
				if(result.data.error)
				{
					this.addAlert(result.data.retval, 'alert-danger');
					this.isloading = true;
				}
				else
				{
					let res = {};
					for (var k in result.data.retval) {
						if (result.data.retval[k] === null) {
							const alert = document.createElement('div');
							alert.innerHTML = this.p.t('studierendenantrag', 'error_stg_last_semester');
							alert.className = 'alert alert-warning';
							alert.role = 'alert';
							this.$refs["lvtable" + k.substr(0,1)].append(alert);
							continue;
						}
						let lvs = result.data.retval[k].reduce((obj,lv) => {
							obj[lv.studienplan_lehrveranstaltung_id] = lv;
							return obj;
						}, {});
						for (var lv of Object.values(lvs)) {
							if (!lv.studienplan_lehrveranstaltung_id_parent)
								continue;
							if (!lvs[lv.studienplan_lehrveranstaltung_id_parent])
								console.error('parent not available');
							else {
								if (!lvs[lv.studienplan_lehrveranstaltung_id_parent]._children)
									lvs[lv.studienplan_lehrveranstaltung_id_parent]._children = [];
								lvs[lv.studienplan_lehrveranstaltung_id_parent]._children.push(lv);
							}
						}
						res[k] = Object.values(lvs).filter(lv => !lv.studienplan_lehrveranstaltung_id_parent);
						let current = res[k];
						let index = k.substr(0,1);
						var table = new Tabulator(this.$refs["lvtable" + k.substr(0,1)], {
							data: current,
							dataTree: true,
							dataTreeStartExpanded: true, //start with an expanded tree
							dataTreeChildIndent: 15,
							layout: "fitDataStretch",
							columns: [
								{title: this.p.t('ui','bezeichnung'), field: "bezeichnung"},
								{title: this.p.t('lehre','lehrform'), field: "lehrform_kurzbz"},
								{title: "ECTS", field: "ects"},
								{title: this.p.t('lehre','note'), field: "note", formatter:(cell, formatterParams, onRendered)=>cell.getValue() || "---"},
								{title: (index==1) ? this.p.t('studierendenantrag','lv_nicht_zulassen') : this.p.t('studierendenantrag','lv_wiederholen'), field: "antrag_zugelassen", formatter: (cell, formatterParams, onRendered) => {
									let data = cell.getData();
									if(data._children || !data.zeugnis)
										return "";
									let input = document.createElement('input');
									input.className = "form-check-input";
									input.type = "checkbox";
									input.role = "switch";
									input.checked = cell.getValue();
									input.addEventListener('input', () => {
										lvs[data.studienplan_lehrveranstaltung_id].antrag_zugelassen = input.checked;
										cell.getRow().reformat();
									});
									if (this.disabled) {
										input.disabled = true;
									}

									let div =  document.createElement('div');
									div.className = 'form-check form-switch';
									div.append(input);

									return div;
								}},
								{
									title: this.p.t('global','anmerkung'),
									field: "antrag_anmerkung",
									headerSort:false,
									titleFormatter:(cell, formatterParams, onRendered)=>{
										let link =  document.createElement('a');
										link.addEventListener('click', (e) => {
											e.preventDefault();
										});

										link.href ="#";
										link.title = this.p.t('studierendenantrag','anmerkung_tooltip');
										new bootstrap.Tooltip(link);
										let tooltip = document.createElement('span');
										tooltip.innerHTML = this.p.t('global','anmerkung') + " ";
										tooltip.append(link);

										let icon =  document.createElement('i');
										link.append(icon);
										icon.className = "fa fa-info-circle";
										icon.setAttribute("aria-hidden", "true");
										icon.style.minWidth = '1em';

										return tooltip;

									},
									formatter: (cell, formatterParams, onRendered) => {
										if (this.disabled) {
											return cell.getValue() || "";
										}
										var data = cell.getData();
										if (lvs[data.studienplan_lehrveranstaltung_id].antrag_zugelassen)
										{
											let input = document.createElement('input');
											input.className = "form-control";
											input.type = "text";
											input.value = cell.getValue() || "";
											input.addEventListener('input', () => {
												lvs[data.studienplan_lehrveranstaltung_id].antrag_anmerkung = input.value;
											});
											return input;
										}
										else
										{
											return "";
										}
									}
								}
							]
						});
					}
					this.lvs = result.data.retval;
				}
			}
		);
	},
	template: `
	<div class="col-sm-8">
		<div ref="alertbox"></div>

		<span class="d-flex justify-content-between h4">
			<span>{{p.t('studierendenantrag', 'title_lv_nicht_zugelassen')}}</span>
			<span>{{lvs1sem}}</span>
		</span>
		<div ref="lvtable1" class="mb-3">
		</div>

		<span class="d-flex justify-content-between h4">
			<span>{{p.t('studierendenantrag', 'title_lv_wiederholen')}}</span>
			<span>{{lvs2sem}}</span>
		</span>
		<div ref="lvtable2">
		</div>

		<button type="button" @click="save" :disabled="isloading || disabled"  class="btn btn-primary my-3">{{p.t('studierendenantrag', 'btn_save_lvs')}}</button>
	</div>
	<div class="col-sm-4">
		<studierendenantrag-status :msg="statusMsg" :severity="statusSeverity"></studierendenantrag-status>
	</div>
	`
}
