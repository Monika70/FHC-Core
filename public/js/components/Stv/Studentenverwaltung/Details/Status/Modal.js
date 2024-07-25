import BsModal from "../../../../Bootstrap/Modal.js";
import CoreForm from '../../../../Form/Form.js';
import FormValidation from '../../../../Form/Validation.js';
import FormInput from '../../../../Form/Input.js';

export default{
	components: {
		BsModal,
		CoreForm,
		FormValidation,
		FormInput
	},
	inject: {
		defaultSemester: {
			from: 'defaultSemester',
		},
		hasPrestudentstatusPermission: {
			from: 'hasPrestudentstatusPermission',
			default: false
		},
		lists: {
			from: 'lists'
		},
		$reloadList: {
			from: '$reloadList',
			required: true
		}
	},
	emit: [
		'saved'
	],
	props: {
		meldestichtag: {
			type: Date,
			required: true
		}
	},
	data() {
		return {
			prestudent: {},
			orig_datum: null,
			statusNew: true,
			statusId: {},
			formData: {},
			listStudienplaene: [],
			listStatusgruende: [],
			// TODO(chris): IMPLEMENT!
			maxSem:  Array.from({ length: 11 }, (_, index) => index),
		};
	},
	computed: {
		bisLocked() {
			// TODO(chris): special right
			if (this.statusNew)
				return false;

			if (!this.orig_datum || !this.meldestichtag)
				return true;
			
			return this.orig_datum < this.meldestichtag;
		},
		isStatusBeforeStudent() {
			let isStatusStudent = ['Student', 'Absolvent', 'Diplomand'];
			return !isStatusStudent.includes(this.formData.status_kurzbz);
		},
		gruende() {
			return this.listStatusgruende.filter(grund => grund.status_kurzbz == this.formData.status_kurzbz);
		}
	},
	methods: {
		open(prestudent, status_kurzbz, studiensemester_kurzbz, ausbildungssemester) {
			this.$refs.modal.hide();
			this.prestudent = prestudent;
			if (!status_kurzbz && !studiensemester_kurzbz && !ausbildungssemester) {
				this.statusNew = true;
				this.statusId = prestudent.prestudent_id;
				this.formData = {
					status_kurzbz: 'Interessent',
					studiensemester_kurzbz: this.defaultSemester,
					ausbildungssemester: 1,
					datum: new Date(),
					bestaetigtam: new Date(),
					bewerbung_abgeschicktamum: null,
					studienplan_id: null,
					anmerkung: null,
					rt_stufe: null,
					statusgrund_id: null
				};
				this.orig_datum = null;
				this.$refs.form.clearValidation();
				this.$refs.modal.show();
			} else {
				this.statusId = {
					prestudent_id: prestudent.prestudent_id,
					status_kurzbz,
					studiensemester_kurzbz,
					ausbildungssemester
				};
				this.$fhcApi
					.post('api/frontend/v1/stv/status/loadStatus/', this.statusId)
					.then(result => {
						this.statusNew = false;
						this.formData = result.data;
						this.orig_datum = new Date(result.data.datum);
						this.$refs.form.clearValidation();
						this.$refs.modal.show();
					})
					.catch(this.$fhcAlert.handleSystemError);
			}
		},
		insertStatus() {
			this.$refs.form
				.post(
					'api/frontend/v1/stv/status/insertStatus/' + this.statusId,
					this.formData
				)
				.then(result => {
					this.$reloadList();
					this.emit('saved');
					this.$refs.modal.hide();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		editStatus() {
			this.$refs.form
				.post(
					'api/frontend/v1/stv/status/updateStatus/' + this.statusId.join('/'),
					this.formData
				)
				.then(result => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.$reloadList();
					this.emit('saved');
					this.$refs.modal.hide();
				})
				.catch(this.$fhcAlert.handleSystemError);
		}
	},
	created() {
		// TODO(chris): reload studienpläne on prestudent change
		this.$fhcApi
			.get('api/frontend/v1/stv/prestudent/getStudienplaene/' + this.prestudent.prestudent_id)
			.then(result => result.data)
			.then(result => {
				this.listStudienplaene = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi
			.get('api/frontend/v1/stv/status/getStatusgruende/')
			.then(result => result.data)
			.then(result => {
				this.listStatusgruende = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<bs-modal class="stv-status-modal" ref="modal">
		<template #title>
			TODO: {{ $p.t('lehre', statusNew ? 'status_new' : 'status_edit', prestudent) }}
		</template>

		<core-form ref="form">
			
			<form-validation></form-validation>
			
			<p v-if="bisLocked && !isStatusBeforeStudent">
				<b>{{$p.t('bismeldestichtag', 'info_MeldestichtagStatusgrund')}}</b>
			</p>
			<p v-if="bisLocked && isStatusBeforeStudent">
				<b>{{$p.t('bismeldestichtag', 'info_MeldestichtagStatusgrundSemester')}}</b>
			</p>
			
			<form-input
				container-class="mb-3"
				type="select"
				v-model="formData.status_kurzbz"
				name="status_kurzbz"
				:label="$p.t('lehre/status_rolle')"
				required
				:disabled="!statusNew"
				>
				<option value="Interessent">InteressentIn</option>
				<option value="Bewerber">BewerberIn</option>
				<option value="Aufgenommener">Aufgenommene/r</option>
				<option value="Student">StudentIn</option>
				<option value="Unterbrecher">UnterbrecherIn</option>
				<option value="Diplomand">DiplomandIn</option>
				<option value="Incoming">Incoming</option>
				<option v-if="!statusNew" value="Absolvent">Absolvent</option>
				<option v-if="!statusNew" value="Abbrecher">Abbrecher</option>
				<option v-if="!statusNew" value="Abgewiesener">Abgewiesener</option>
			</form-input>
			<form-input
				container-class="mb-3"
				type="select"
				v-model="formData.studiensemester_kurzbz"
				name="studiensemester_kurzbz"
				:label="$p.t('lehre/studiensemester')"
				:disabled="bisLocked"
				>
				<option
					v-for="sem in lists.studiensemester_desc"
					:key="sem.studiensemester_kurzbz"
					:value="sem.studiensemester_kurzbz"
					>
					{{ sem.studiensemester_kurzbz }}
				</option>
			</form-input>
			<!-- TODO(manu) if(defined('VORRUECKUNG_STATUS_MAX_SEMESTER') && VORRUECKUNG_STATUS_MAX_SEMESTER==false) 100 Semester-->
			<form-input
				container-class="mb-3"
				type="select"
				v-model="formData.ausbildungssemester"
				name="ausbildungssemester"
				:label="$p.t('lehre/ausbildungssemester')"
				:disabled="bisLocked && !isStatusBeforeStudent"
				>
				<option
					v-for="number in maxSem"
					:key="number"
					:value="number"
					>
					{{ number }}
				</option>
			</form-input>
			<form-input
				container-class="mb-3"
				type="DatePicker"
				v-model="formData.datum"
				name="datum"
				:label="$p.t('global/datum')"
				auto-apply
				:enable-time-picker="false"
				format="dd.MM.yyyy"
				preview-format="dd.MM.yyyy"
				:teleport="true"
				:disabled="bisLocked"
				>
			</form-input>
			<form-input
				container-class="mb-3"
				type="DatePicker"
				v-model="formData.bestaetigtam"
				name="bestaetigtam"
				:label="$p.t('lehre/bestaetigt_am')"
				auto-apply
				:enable-time-picker="false"
				format="dd.MM.yyyy"
				preview-format="dd.MM.yyyy"
				:teleport="true"
				:disabled="bisLocked"
				>
			</form-input>
			<form-input
				container-class="mb-3"
				type="DatePicker"
				v-model="formData.bewerbung_abgeschicktamum"
				name="bewerbung_abgeschicktamum"
				:label="$p.t('lehre/bewerbung_abgeschickt_am')"
				auto-apply
				:enable-time-picker="false"
				format="dd.MM.yyyy"
				preview-format="dd.MM.yyyy"
				:teleport="true"
				:disabled="bisLocked || !hasPrestudentstatusPermission"
				>
			</form-input>
			<form-input
				container-class="mb-3"
				type="select"
				v-model="formData.studienplan_id"
				name="studienplan_id"
				:label="$p.t('lehre/studienplan')"
				:disabled="bisLocked"
				>
				<option
					v-for="sp in listStudienplaene"
					:key="sp.studienplan_id"
					:value="sp.studienplan_id"
					>
					{{ sp.bezeichnung }}
				</option>
			</form-input>
			<form-input
				container-class="mb-3"
				type="text"
				v-model="formData.anmerkung"
				name="anmerkung"
				:label="$p.t('global/anmerkung')"
				:disabled="bisLocked"
				>
			</form-input>
			<form-input
				container-class="mb-3"
				type="select"
				v-model="formData.rt_stufe"
				name="rt_stufe"
				:label="$p.t('lehre/aufnahmestufe')"
				:disabled="bisLocked"
				>
				<option :value="null">-- {{$p.t('fehlermonitoring', 'keineAuswahl')}} --</option>
				<option v-for="entry in [1,2,3]" :key="entry" :value="entry">{{entry}}</option>
			</form-input>
			<form-input
			 	v-if="gruende.length"
			 	container-class="mb-3"
				type="select"
				v-model="formData.statusgrund_id"
				name="statusgrund_id"
				:label="$p.t('studierendenantrag/antrag_grund')"
				>
				<option :value="null">-- {{$p.t('fehlermonitoring', 'keineAuswahl')}} --</option>
				<option
					v-for="grund in gruende"
					:key="grund.statusgrund_id"
					:value="grund.statusgrund_id"
					>
					{{ grund.bezeichnung }}
				</option>
			</form-input>
		
		</core-form>
		
		<template #footer>
		<button
			type="button"
			class="btn btn-primary"
			@click="statusNew ? insertStatus() : editStatus()"
			>
			{{ $p.t('ui', 'ok') }}
		</button>
		</template>
	</bs-modal>`
};