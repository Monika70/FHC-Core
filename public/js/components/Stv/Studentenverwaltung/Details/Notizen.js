import NotizComponent from "../../../Notiz/NotizComponent.js";

export default {
	components: {
		NotizComponent
	},
	props: {
		modelValue: Object
	},
	template: `
	<div class="stv-details-details h-100 pb-3">
		<h3>Notizen</h3>
		<NotizComponent
			ref="formc"
			typeId="person_id"
			:id="modelValue.person_id"
			:showErweitert=true
			:showDocument=true
			:showTinyMCE="true"
			>
		</NotizComponent>
		
<!--		<br><br>
		<h3>Test prestudentId</h3>
		<NotizComponent
			ref="formc"
			typeId="prestudent_id"
			:id="modelValue.prestudent_id"
			>
		</NotizComponent>
		
		<br><br>
		<h3>Test mitarbeiter_uid</h3>
		<NotizComponent
			ref="formc"
			typeId="uid"
			:id="'ma0068'"
			>
		</NotizComponent>-->
		
<!--		<br><br>
		<h3>Test projekt</h3>
		<NotizComponent
			ref="formc"
			typeId="projekt_kurzbz"
			:id="'Studentenausweis'"
			>
		</NotizComponent>-->
		
	</div>
	`
};
