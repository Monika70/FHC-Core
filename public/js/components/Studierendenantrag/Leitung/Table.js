import BsModal from '../../Bootstrap/Modal.js';
import {CoreFetchCmpt} from '../../Fetch.js';
import LvPopup from './LvPopup.js';
import { dateFilter } from '../../../tabulator/filters/Dates.js';

export default {
	components: {
		BsModal,
		CoreFetchCmpt,
		LvPopup
	},
	props: {
		selectedData: Array,
		columnData: Array,
		stgL: Array,
		stgA: Array,
		filter: String
	},
	emits: [
		'update:columnData',
		'update:selectedData',
		'action:approve',
		'action:reject',
		'action:reopen',
		'action:object',
		'action:objectionDeny',
		'action:objectionApprove',
		'action:cancel'
	],
	data() {
		return {
			ajaxUrl: FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router +
				'/components/Antrag/Leitung/getAntraege/',
			table: null,
			lastHistoryClickedId: null,
			historyData: [],
			lvsData: null
		}
	},
	methods: {
		reload(stg) {
			this.table.setData(this.ajaxUrl + (stg || ''));
		},
		download() {
			this.table.download("csv", "data.csv", {
				delimiter: ';',
				bom: true
			});
		},
		getHistory() {
			if (this.lastHistoryClickedId === null)
				return null;
			return axios.get(
				FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router +
				'/components/Antrag/Leitung/getHistory/' +
				this.lastHistoryClickedId
			).then(res => {
				this.historyData = res.data.retval.sort((a, b) => a.insertamum > b.insertamum);
			});
		},
		showHistoryGrund(grund) {
			this.$refs.modalGrund.$el.addEventListener(
				'hidden.bs.modal',
				this.$refs.history.show,
				{
					once: true
				}
			);
			this.$refs.modalGrundPre.innerHTML = grund;
		},
		showLVs(data) {
			this.lvsData = data;
			this.$refs.lvList.show();
		}
	},
	async mounted() {
		await this.$p.loadCategory(['lehre', 'studierendenantrag', 'person', 'global', 'ui']);
		function dateFormatter(cell)
		{
			let val = cell.getValue();
			if (!val)
				return '&nbsp;';
			let date = new Date(val);
			return date.toLocaleDateString();
		}

		this.table = new Tabulator(this.$refs.table, {
			placeholder:"Keine zu bearbeitenden Datensätze",
			movableColumns: true,
			maxHeight: '50vh',
			layout: "fitDataFill",
			ajaxURL: this.ajaxUrl + (this.filter || ''),
			persistence: { // NOTE(chris): do not store column titles
				sort: true, //persist column sorting
				filter: true, //persist filters
				headerFilter: true, //persist header filters
				group: true, //persist row grouping
				page: true, //persist page
				columns: ["width", "visible"], //persist columns
			},
			persistenceID: 'studierendenantrag_leitung_2023-11-14',
			columns: [{
				formatter: 'rowSelection',
				titleFormatter: 'rowSelection',
				titleFormatterParams: {
					rowRange: 'active'
				},
				hozAlign: 'center',
				headerSort: false
			}, {
				field: 'studierendenantrag_id',
				title: '#',
				sorter: 'number'
			}, {
				field: 'bezeichnung',
				title: this.$p.t('lehre', 'studiengang'),
				headerFilter: 'list',
				headerFilterParams: {
					valuesLookup: true,
					clearable: true,
					autocomplete: true,
				}
			}, {
				field: 'orgform',
				title: this.$p.t('lehre', 'organisationsform'),
				headerFilter: 'list',
				headerFilterParams: {
					valuesLookup: true,
					clearable: true,
					autocomplete: true,
				}
			}, {
				field: 'typ',
				title: this.$p.t('studierendenantrag', 'antrag_typ'),
				headerFilter: 'list',
				headerFilterParams: {
					valuesLookup: true,
					clearable: true,
					autocomplete: true,
				},
				formatter: (cell, formatterParams, onRendered) => {
					return this.$p.t('studierendenantrag','antrag_typ_' + cell.getValue());
				}
			}, {
				field: 'statustyp',
				title: this.$p.t('studierendenantrag', 'antrag_status'),
				headerFilter: 'list',
				headerFilterParams: {
					valuesLookup: true,
					clearable: true,
					autocomplete: true,
				},
				formatter: (cell, formatterParams, onRendered) => {
					let link = document.createElement('a');
					link.href = "#";
					link.innerHTML = cell.getValue();
					link.addEventListener('click', e => {
						e.preventDefault();
						this.lastHistoryClickedId = cell.getData().studierendenantrag_id;
						this.$refs.historyLoader.fetchData();
						this.$refs.history.show();
					});

					return link;
				}
			}, {
				field: 'matrikelnr',
				title: this.$p.t('person', 'personenkennzeichen'),
				headerFilter: 'input'
			}, {
				field: 'prestudent_id',
				title: this.$p.t('lehre', 'prestudent'),
				headerFilter: 'input'
			}, {
				field: 'name',
				title: this.$p.t('global', 'name'),
				mutator: (value, data) => (data.vorname + ' ' + data.nachname).replace(/^\s*(.*)\s*$/, '$1'),
				headerFilter: 'input'
			}, {
				field: 'datum',
				title: this.$p.t('global', 'datum'),
				formatter: dateFormatter,
				headerFilterFunc: 'dates',
				headerFilter: dateFilter
			}, {
				field: 'datum_wiedereinstieg',
				title: this.$p.t('studierendenantrag', 'antrag_datum_wiedereinstieg'),
				formatter: dateFormatter,
				headerFilterFunc: 'dates',
				headerFilter: dateFilter
			}, {
				field: 'grund',
				title: this.$p.t('studierendenantrag', 'antrag_grund'),
				formatter: (cell, formatterParams, onRendered) => {
					let link = document.createElement('a'),
						val = cell.getValue();
					link.href = "#modal-grund";
					link.setAttribute('data-bs-toggle', 'modal');
					link.innerHTML = this.$p.t('studierendenantrag', 'antrag_grund');
					link.addEventListener('click', () => {
						this.$refs.modalGrundPre.innerHTML = val;
					});

					return val ? link : '&nbsp;';
				}
			}, {
				field: 'dms_id',
				title: this.$p.t('studierendenantrag', 'antrag_dateianhaenge'),
				formatter: (cell, formatterParams, onRendered) => {
					let val = cell.getValue();
					if (!val)
						return '&nbsp;';
					let link = document.createElement('a');
					link.href = FHC_JS_DATA_STORAGE_OBJECT.app_root +
						FHC_JS_DATA_STORAGE_OBJECT.ci_router +
						'/lehre/Antrag/Attachment/show/' +
						val;
					link.setAttribute('target', '_blank');
					link.innerHTML = '<i class="fa fa-paperclip" aria-hidden="true"></i>';
					link.append(this.$p.t('studierendenantrag/antrag_anhang'));
					return link;
				}
			}, {
				field: 'actions',
				frozen: true,
				title: this.$p.t('ui', 'aktion'),
				headerFilter: false,
				headerSort: false,				
				formatter: (cell, formatterParams, onRendered) => {
					let container = document.createElement('div'),
						data = cell.getData();

					container.className = "d-flex gap-2";

					if ((data.typ == 'Abmeldung' || data.typ == 'AbmeldungStgl' || data.typ == 'Unterbrechung') && (data.status == 'Genehmigt' || data.status == 'Beeinsprucht' || data.status == 'EinspruchAbgelehnt' || data.status == 'EmailVersandt')) {
						// NOTE(chris): Download PDF
						let button = document.createElement('a');
						// NOTE(chris): phrasen in attribues don't work if they are not preloaded
						// it work in this case because the category has already been loaded before
						button.innerHTML = '<i class="fa-solid fa-download" title="' + this.$p.t('studierendenantrag', 'btn_download_antrag') + '"></i>';
						button.className = "btn btn-outline-secondary";
						button.target = "_blank";
						button.href = FHC_JS_DATA_STORAGE_OBJECT.app_root +
							'content/pdfExport.php?xml=Antrag' + data.typ + '.xml.php&xsl=Antrag' + data.typ + '&id=' + data.studierendenantrag_id + '&output=pdf';
						container.append(button);
					}
					if (data.typ == 'AbmeldungStgl' && data.status == 'Genehmigt') {
						// NOTE(chris): Object
						let button = document.createElement('button');
						button.append(this.$p.t('studierendenantrag', 'btn_object'));
						button.className = "btn btn-outline-secondary";
						button.addEventListener('click', () => this.$emit('action:object', [cell.getData()]));
						container.append(button);
					}

					if (data.typ == 'AbmeldungStgl' && data.status == 'Beeinsprucht') {
						// NOTE(chris): Deny Objection
						let button = document.createElement('button');
						button.append(this.$p.t('studierendenantrag', 'btn_objection_deny'));
						button.className = "btn btn-outline-secondary";
						button.addEventListener('click', () => this.$emit('action:objectionDeny', [cell.getData()]));
						container.append(button);

						// NOTE(chris): Approve Objection
						button = document.createElement('button');
						button.append(this.$p.t('studierendenantrag', 'btn_objection_approve'));
						button.className = "btn btn-outline-secondary";
						button.addEventListener('click', () => this.$emit('action:objectionApprove', [cell.getData()]));
						container.append(button);
					}

					if (this.stgA.includes(data.studiengang_kz)) {
						// NOTE(chris): Reopen
						if (data.typ == 'Wiederholung' && data.status == 'Verzichtet') {
							let button = document.createElement('button');
							button.append(this.$p.t('studierendenantrag', 'btn_reopen'));
							button.className = "btn btn-outline-secondary";
							button.addEventListener('click', () => this.$emit('action:reopen', [cell.getData()]));
							container.append(button);
						}
						// NOTE(chris): Lv Zuweisen
						if (data.typ == 'Wiederholung' && (data.status == 'Erstellt' || data.status == 'Lvszugewiesen')) {
							let button = document.createElement('a');
							button.append(this.$p.t('studierendenantrag', 'btn_lvzuweisen'));
							button.className = "btn btn-outline-secondary";
							button.href = FHC_JS_DATA_STORAGE_OBJECT.app_root +
								FHC_JS_DATA_STORAGE_OBJECT.ci_router +
								'/lehre/Antrag/Wiederholung/assistenz/' +
								cell.getData().studierendenantrag_id + '/frame';
							button.onclick = e => {
								e.preventDefault();
								BsModal.popup(Vue.h('iframe', {
									src: button.href,
									class: 'position-absolute top-0 start-0 w-100 h-100'
								}), {
									dialogClass: 'modal-fullscreen'
								}, this.$p.t('studierendenantrag', 'title_lvzuweisen', cell.getData())).then(() => {
									this.$emit('reload');
								});
							};
							container.append(button);
						}
						// NOTE(chris): Cancel
						if (data.typ == 'AbmeldungStgl' && (data.status == 'Erstellt' || data.status == 'Genehmigt' )) {
							let button = document.createElement('button');
							button.append(this.$p.t('studierendenantrag', 'btn_cancel'));
							button.className = "btn btn-outline-secondary";
							button.addEventListener('click',() => this.$emit('action:cancel', [cell.getData()]));
							container.append(button);
						}
					}

					if (this.stgL.includes(data.studiengang_kz)) {
						// NOTE(chris): Approve
						if ((data.typ == 'Wiederholung' && data.status == 'Lvszugewiesen') || (data.typ != 'Wiederholung' && data.status == 'Erstellt')) {
							let button = document.createElement('button');
							button.append(this.$p.t('studierendenantrag', 'btn_approve'));
							button.className = "btn btn-outline-secondary";
							button.addEventListener('click', () => this.$emit('action:approve', [cell.getData()]));
							container.append(button);
						}
						// NOTE(chris): Reject (Unterbrechung braucht grund)
						if (data.status == 'Erstellt' && data.typ == 'Unterbrechung') {
							let button = document.createElement('button');
							button.append(this.$p.t('studierendenantrag', 'btn_reject'));
							button.className = "btn btn-outline-secondary";
							button.addEventListener('click', () => this.$emit('action:reject', [cell.getData()]));
							container.append(button);
						}
					}

					// NOTE(chris): Show LVs
					if (data.typ == 'Wiederholung' && (data.status == 'Lvszugewiesen' || data.status == 'Genehmigt')) {
						let button = document.createElement('button');
						button.append(this.$p.t('studierendenantrag', 'btn_show_lvs'));
						button.className = "btn btn-outline-secondary";
						button.addEventListener('click', () => this.showLVs(cell.getData()));
						container.append(button);
					}

					if (container.innerHTML == '')
						container.innerHTML = '&nbsp;';

					return container;
				}
			}]
		});
		this.table.on("tableBuilt", () => {
			let columns = this.table.getColumns();
			let columnData = [];
			for (let col of columns) {
				let def = col.getDefinition();
				if (def.title && !def.frozen) {
					columnData.push({
						title: def.title,
						visible: col.isVisible(),
						original: col
					});
				}
			}
			this.$emit('update:columnData', columnData);
		});
		this.table.on("rowSelectionChanged", data => {
			this.$emit('update:selectedData', data);
		});
	},
	template: `
	<div class="studierendenantrag-leitung-table">
		<div ref="table"></div>
		<bs-modal ref="modalGrund" id="modal-grund" class="fade">
			<template #title>{{$p.t('studierendenantrag', 'antrag_grund')}}</template>
			<textarea class="form-control" ref="modalGrundPre" style="width: 100%; height: 250px;" readonly></textarea>
		</bs-modal>
		<bs-modal ref="history" class="fade">
			<template #title>{{$p.t('studierendenantrag', 'title_history', {id: lastHistoryClickedId})}}</template>
			<core-fetch-cmpt ref="historyLoader" :api-function="getHistory">
				<table v-if="historyData.length" class="table">
					<tr v-for="status in historyData" :key="status.studierendenantrag_status_id">
						<td>{{(new Date(status.insertamum)).toLocaleString()}}</td>
						<td>{{status.insertvon}}</td>
						<td>{{status.typ}}</td>
						<td>
							<a v-if="status.grund" href="#modal-grund" data-bs-toggle="modal" @click="showHistoryGrund(status.grund)">
								{{$p.t('studierendenantrag', 'antrag_grund')}}
							</a>
						</td>
					</tr>
				</table>
			</core-fetch-cmpt>
		</bs-modal>
		<lv-popup ref="lvList" class="fade" :antrag-id="lvsData ? lvsData.studierendenantrag_id : null">
			{{$p.t('studierendenantrag', 'title_show_lvs', lvsData ? lvsData : {name: ''}) }}
		</lv-popup>
	</div>
	`
}
